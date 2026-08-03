# The Shop — Backend (Laravel)

## Introduction

**The Shop** is a full-stack e-commerce system. The **backend** is a Laravel 12 application that powers a versioned **REST JSON API** (`/api/v1/...`) consumed by a Vue 3 single-page application, and a **separate server-rendered admin area** (Blade templates, Bootstrap) for catalog and order management. The same codebase handles **user authentication** (Laravel Sanctum personal access tokens, email/password registration and login, and **“Sign in with Google”** via Laravel Socialite), **shopping cart, wishlist, orders, product reviews, and coupons**, and integrates **Stripe Checkout** for payments.

**Local Docker:** prefer [Laravel Sail + custom nginx](../docs/SAIL.md) (`https://shop-local.codecreators.co.uk` with `/`, `/api`, `/admin`). Host `php artisan serve` remains supported.

Engineering practices reflected in the code include: **form request validation** for API and admin input, **authorization policies** (e.g. order ownership, admin actions), a **standardized JSON envelope** for API responses, **Eloquent** relationships and migrations, **database seeders and model factories** for realistic demo data, **background jobs** for transactional email (with the database queue in development/production), **caching configuration**, **API rate limiting**, **PHPStan (Larastan)** static analysis, and a **feature + unit** test suite aligned with the GitHub Actions CI pipeline.

The goal of this document is to let another developer **clone the repository, install dependencies, configure environment variables, run migrations and workers, and use the API, admin panel, email, jobs, Google OAuth, and Stripe** in a local environment on macOS (or any OS that supports PHP and Composer), and to know how to **run tests, coverage, and static analysis** the same way CI does.

**Full HTTP API (every route, request bodies, cURL examples):** [`../docs/API.md`](../docs/API.md).  
**Sail / local Docker gateway:** [`../docs/SAIL.md`](../docs/SAIL.md).

---

## Table of contents

1. [Prerequisites](#prerequisites)  
2. [Clone the repository](#clone-the-repository)  
3. [Install dependencies](#install-dependencies)  
4. [Environment configuration (`.env`)](#environment-configuration-env)  
5. [Application key, database, migrate & seed](#application-key-database-migrate--seed)  
6. [Storage link](#storage-link)  
7. [Run the application (API + admin)](#run-the-application-api--admin)  
8. [Queue workers (emails and async jobs)](#queue-workers-emails-and-async-jobs)  
9. [Where things live: URLs](#where-things-live-urls)  
10. [Admin panel: features and access](#admin-panel-features-and-access)  
11. [Mail (development and test providers)](#mail-development-and-test-providers)  
12. [Background jobs: what runs and when](#background-jobs-what-runs-and-when)  
13. [Stripe (test payments)](#stripe-test-payments)  
14. [Google OAuth (Sign in with Google)](#google-oauth-sign-in-with-google)  
15. [Testing](#testing)  
16. [Static analysis (PHPStan)](#static-analysis-phpstan)  
17. [Code style (Laravel Pint)](#code-style-laravel-pint)  
18. [Health check](#health-check)  
19. [Troubleshooting](#troubleshooting)  
20. [Quick commands & route discovery](#quick-commands--route-discovery)  
21. [Project documentation index (`docs/`)](#project-documentation-index-docs)

---

## Prerequisites

- **PHP** `^8.2` (the project’s `composer.json` requirement; CI on GitHub uses PHP 8.4).  
- **Composer 2**  
- **Extensions** typically required by Laravel: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo` (and `curl` for HTTP clients, OAuth, Stripe). For **SQLite** (default in `.env.example`): `pdo_sqlite`. For **MySQL**: `pdo_mysql`.  
- For **code coverage** reports: `Xdebug` or `PCOV` (see [Testing – Coverage](#coverage)).  
- **Git**  
- A running **MySQL/PostgreSQL** instance only if you choose not to use the default **SQLite** file database.  
- The **Vue frontend** (optional for backend-only work) needs **Node.js**; see the `frontend/` README. To see the full product (SPA + API + OAuth redirects + Stripe return URLs), run **both** backend and frontend with consistent `APP_URL` and `FRONTEND_URL`.

---

## Clone the repository

```bash
git clone <your-fork-or-upstream-url> the-shop
cd the-shop
```

All backend commands below are run from the **`backend/`** directory unless noted.

```bash
cd backend
```

Use your real Git remote URL in place of `<your-fork-or-upstream-url>`.

---

## Install dependencies

```bash
composer install
```

This installs Laravel 12, **Sanctum**, **Socialite**, **Stripe PHP**, development tools such as **Laravel Pint**, **PHPStan / Larastan**, **Debugbar**, and **PHPUnit 11**.

---

## Environment configuration (`.env`)

1. Copy the example file:

   ```bash
   cp .env.example .env
   ```

2. Generate an application key:

   ```bash
   php artisan key:generate
   ```

3. **Important variables** (see inline comments in `.env.example`):

   | Group | Purpose |
   |--------|--------|
   | `APP_URL` | Public base URL of this Laravel app (no trailing slash), e.g. `http://127.0.0.1:8000`. Used for signed URLs, Google redirect resolution, and asset URLs. |
   | `DB_*` | Default in `.env.example` is **SQLite** (`DB_CONNECTION=sqlite`). Ensure `database/database.sqlite` exists (see [migrate & seed](#application-key-database-migrate--seed)). |
   | `FRONTEND_URL` | Origin of the Vue SPA, e.g. `http://localhost:5173`. Used after Google OAuth and in emails linking to product pages. |
   | `MAIL_*` | Transactional email (order confirmation, verification, welcome). |
   | `STRIPE_SECRET_KEY` / `STRIPE_PUBLIC_KEY` | **Required** for real checkout sessions (see [Stripe](#stripe-test-payments)). |
   | `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` / `GOOGLE_REDIRECT_URI` | **Required** for Google sign-in. `GOOGLE_REDIRECT_URI` must match a URI in Google Cloud Console; default expands from `APP_URL` to `.../api/v1/auth/google/callback`. |
   | `QUEUE_CONNECTION` | Default `database` — requires a [queue worker](#queue-workers-emails-and-async-jobs) for queued jobs. Use `sync` if you want jobs to run in-process without a worker (simpler local setup). |
   | `SESSION_DRIVER` | `database` in `.env.example` — after `migrate`, the `sessions` table exists. Google OAuth uses **web** session middleware for Socialite state. |
   | `CACHE_STORE` / `FILESYSTEM_DISK` | Defaults suit local dev; S3 is optional in `.env.example`. |

4. For **CI-style tests** without replacing your main `.env`, rely on `phpunit.xml` environment and `.env.testing` (see [Testing](#testing)). The GitHub workflow uses `cp .env.testing .env` only inside the action runner.

---

## Application key, database, migrate & seed

### SQLite (quickest, matches default `.env.example`)

```bash
touch database/database.sqlite
```

### MySQL (optional)

Set `DB_CONNECTION=mysql` and fill `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` in `.env`, then create the empty database in MySQL.

### Migrate and seed

```bash
php artisan migrate
php artisan db:seed
```

**Seeded content** includes: **admin user** (via `AdminSeeder` + `AdminFactory`), **categories, brands, colors, sizes, coupons**, **demo customers** (`UserSeeder` includes known accounts; passwords are `password` where the seeder uses `Hash::make('password')`), **products, orders, reviews, cart, wishlist** sample data.

**Default admin login** (from `database/factories/AdminFactory.php` as used by `AdminSeeder`):

- **URL:** `http://127.0.0.1:8000/admin` (use your `APP_URL` if different)  
- **Email:** `admin@email.com`  
- **Password:** `password`  

Change these in production; never use factory defaults on public systems.

---

## Storage link

Product images, Google profile avatars, and public assets use the `public` disk. Create the symbolic link if you have not already:

```bash
php artisan storage:link
```

---

## Run the application (API + admin)

From `backend/`:

```bash
php artisan serve
```

Serves the app at `http://127.0.0.1:8000` by default.

- **API base:** e.g. `http://127.0.0.1:8000/api/v1/...`  
- **Admin UI:** `http://127.0.0.1:8000/admin`  

For the **Vue frontend**, from `../frontend` run `npm install` and `npm run dev` (or the scripts in that package’s `package.json`), with `FRONTEND_URL` matching the Vite dev server origin (often port **5173**).

---

## Queue workers (emails and async jobs)

With `QUEUE_CONNECTION=database` (default in `.env.example`):

- Queued classes such as `SendOrderConfirmationEmail` and `SendVerificationEmail` are **stored in the `jobs` table** until a worker processes them.  
- In a second terminal:

```bash
php artisan queue:work
```

**Without a running worker:** queued jobs will not be processed. For quick local tests you may temporarily set `QUEUE_CONNECTION=sync` in `.env` so jobs run inline — switch back to `database` when testing queue behavior or mirroring production.

`SendWelcomeEmail` does **not** implement `ShouldQueue` and is handled **synchronously** when dispatched, with an internal `retry()` for mail provider rate limits.

---

## Where things live: URLs

| Resource | Path / pattern |
|----------|----------------|
| **API (versioned)** | `/api/v1/...` |
| **Sanctum-protected user routes** | e.g. `/api/v1/user`, `/api/v1/cart`, `/api/v1/orders`, `POST /api/v1/orders/pay` — see `routes/api.php`. |
| **Public product routes** | e.g. `GET /api/v1/products`, category/brand/color/size filters, search. |
| **Auth** | `POST /api/v1/user/register`, `POST /api/v1/user/login`, email verification, `POST /api/v1/user/logout`, etc. |
| **Google OAuth** | `GET /api/v1/auth/google`, `GET /api/v1/auth/google/callback` (group uses `web` middleware for session + Socialite). |
| **Admin** | `GET /admin` (login), `POST /admin/auth`, dashboard and CRUD under `/admin/...` with `admin` middleware. |
| **Health** | `GET /up` |

**JSON contract:** Controllers return a consistent structure (`message`, `data`, `error`, `status` as applicable). The project documents this in `.cursor/rules/laravel-api-responses.mdc`.

---

## Admin panel: features and access

The admin area is **server-rendered** (Blade, Bootstrap), not the Vue app. **Login:** `GET /admin` (then `POST /admin/auth`). Authentication uses the **`admin` guard** (`App\Models\Admin`) and `App\Http\Middleware\AdminMiddleware`.

| Section | What it covers |
|--------|----------------|
| **Dashboard** | Order counts for today, yesterday, current month, and current year. |
| **Categories** | Full CRUD. |
| **Brands** | Full CRUD. |
| **Colors** | Full CRUD. |
| **Sizes** | Full CRUD. |
| **Products** | List, create, show, edit, delete; product image management including **delete image** for a product. |
| **Coupons** | Full CRUD. |
| **Orders** | List (with user, products, coupon). **Update** delivery timestamp (`deliverd_at`). **Delete** (detach pivot, delete order) with policy checks. |
| **Reviews** | List with **filter** (`approved` / `unapproved`); **toggle** approval; **delete**. |
| **Users** | List with filter for **active** vs **soft-deleted**; **soft delete**; **restore** trashed user. |

Feature tests live under `tests/Feature/Admin/`.

---

## Mail (development and test providers)

- **`MAIL_MAILER=log`:** Writes mail to the log — good for verifying mailables without SMTP.  
- **SMTP (Mailtrap, etc.):** set `MAIL_MAILER=smtp` and the host, port, user, password, and encryption. Some hosts block **587/465**; many providers also support **2525**.  
- **Queued** mail: ensure a [queue worker](#queue-workers-emails-and-async-jobs) runs when `QUEUE_CONNECTION=database`.

---

## Background jobs: what runs and when

| Class | Role | Queued? |
|-------|------|--------|
| `App\Jobs\SendVerificationEmail` | Sends verify-email mailable if not yet verified. | Yes (`ShouldQueue`) |
| `App\Jobs\SendWelcomeEmail` | Welcome after verification or new Google user. | No — runs synchronously when dispatched. |
| `App\Jobs\SendOrderConfirmationEmail` | Sends order confirmation mailable. | Yes (`ShouldQueue`) |

**Tests** use `QUEUE_CONNECTION=sync` in `phpunit.xml` / `.env.testing` so no worker is required in CI.

---

## Stripe (test payments)

1. **Stripe Dashboard → API keys** (test mode for development).  
2. Set `STRIPE_SECRET_KEY=sk_test_...` and `STRIPE_PUBLIC_KEY=pk_test_...` in `.env` (`config/services.php` reads `env()` into `stripe`).  
3. **Authenticated** `POST /api/v1/orders/pay` creates a **Stripe Checkout Session** (`App\Http\Controllers\Api\OrderController::payOrdersByStripe`) with `success_url` and `cancel_url` from the validated request; `metadata` includes `user_id` and cart line items.  
4. The SPA opens the returned `data.url` in the browser. Use Stripe’s **test card** numbers in test mode.  
5. Empty or wrong keys will break checkout.  
6. After `.env` changes: `php artisan config:clear` (in production, follow your deployment’s `config:cache` practice).

---

## Google OAuth (Sign in with Google)

1. **Google Cloud Console** → **Credentials** → **OAuth 2.0 Client ID** (Web).  
2. **Authorized redirect URI** must match `GOOGLE_REDIRECT_URI` exactly, e.g. `http://127.0.0.1:8000/api/v1/auth/google/callback`.  
3. Set `GOOGLE_CLIENT_ID` and `GOOGLE_CLIENT_SECRET`.  
4. OAuth routes use the **`web`** middleware so **sessions** work with Socialite.  
5. Success: create Sanctum token, **redirect** to `FRONTEND_URL` with `?token=...` for the SPA.  
6. On failure, redirect to `FRONTEND_URL/login?error=...` (`App\Http\Controllers\Api\Auth\GoogleController`).  
7. `php artisan config:cache` in production only after `GOOGLE_*` and `APP_URL` are final.

**API / Postman:** Use `Accept: application/json` and `Authorization: Bearer {token}` on protected routes. The app renders JSON for API routes on errors where configured in `bootstrap/app.php`.

---

## Testing

### Run the full suite

```bash
php artisan test
```

or:

```bash
./vendor/bin/phpunit
```

### Filtered runs

```bash
php artisan test --filter=UserControllerTest
php artisan test tests/Feature/Api
php artisan test tests/Feature/Admin
```

### What is covered

- **Feature** — `tests/Feature/Api/` (REST, Sanctum, policies, mail fakes where used), `tests/Feature/Admin/` (admin guard, routes).  
- **Unit** — `tests/Unit/` (e.g. `Policies/OrderPolicyTest`, `ColorTest`, `SizeTest`).

### CI parity

The workflow **“CI (backend + frontend)”** in `.github/workflows/deploy.yml` runs, from `backend/`:

- `cp -f .env.testing .env`  
- `composer install`  
- `composer phpstan`  
- `php artisan test`  

### Coverage

Install **PCOV** or enable **Xdebug** (`xdebug.mode=coverage`), then:

```bash
php artisan test --coverage
php artisan test --coverage --coverage-html build/coverage
```

---

## Static analysis (PHPStan)

```bash
composer phpstan
```

To refresh a baseline (only when you intend to update suppressions):

```bash
composer phpstan:baseline
```

This matches the GitHub Actions backend step.

---

## Code style (Laravel Pint)

```bash
./vendor/bin/pint
```

---

## Health check

- **`GET /up`** — Laravel default health route (full URL: `{APP_URL}/up`). Use for uptime checks and load balancers. See also [`../docs/API.md`](../docs/API.md#health-check-get-up).

---

## Quick commands & route discovery

| Task | Command |
|------|--------|
| **List only API routes** | `php artisan route:list --path=api` |
| **Clear cached config** (after changing `.env` in dev) | `php artisan config:clear` |
| **Clear several caches at once** | `php artisan optimize:clear` |
| **Run pending migrations** | `php artisan migrate` |
| **Fresh DB + seed (destructive)** | `php artisan migrate:fresh --seed` |

**Note on `composer run dev` in `backend/`:** the Composer **`dev`** script (see `composer.json`) runs `php artisan serve`, a **queue** listener, **Pail**, and **`npm run dev`** in **this** directory. That targets the small **Laravel Vite** setup in `backend/`, **not** the main **Vue 3** app in `../frontend/`. For the customer SPA, use `npm run dev` from **`../frontend`**.

---

## Project documentation index (`docs/`)

| Document | What it is |
|----------|------------|
| [`../docs/API.md`](../docs/API.md) | Full **HTTP** reference: `/api/v1` JSON, **`GET /up`**, optional **`/sanctum/csrf-cookie`**, and **admin** web routes. |
| [`../docs/CICD.md`](../docs/CICD.md) | **CI/CD**, tags, deploy to the droplet. |

Root workflow and scripts: [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml), `../scripts/`.

---

## Troubleshooting

| Symptom | Things to check |
|--------|------------------|
| **Migrate fails** | `APP_KEY`; SQLite file path; MySQL credentials. |
| **Admin CSRF 419** | `APP_URL` vs browser URL; `SESSION_DRIVER`; session domain. |
| **API 401 / HTML** | `Accept: application/json`; valid Bearer token. |
| **No email** | Queue worker; `MAIL_*`; logs with `log` driver. |
| **Google redirect mismatch** | `GOOGLE_REDIRECT_URI` matches Google Console **exactly**. |
| **Stripe errors** | Test keys; outbound HTTPS to Stripe. |

---

## License

The Laravel framework is open-sourced under the MIT License. Application-level license terms, if any, are at the maintainer’s discretion.

---

## Related documentation

- **Monorepo overview (start here):** [`../README.md`](../README.md)  
- **[`docs/` index](#project-documentation-index-docs)** — `API.md`, `CICD.md`  
- **HTTP API (detail):** [`../docs/API.md`](../docs/API.md)  
- **Frontend (Vue 3):** [`../frontend/README.md`](../frontend/README.md)  
- **CI/CD and deploy:** [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml) and [`../scripts/`](../scripts/)  
- **Releases & CI/CD:** [`../docs/CICD.md`](../docs/CICD.md)

The backend is a **single** service: shared models, policies, and rules power the public API, the admin UI, and background jobs, which keeps the system coherent and testable.
