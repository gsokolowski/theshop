# The Shop — Frontend (Vue 3)

## Introduction

The **frontend** is a **Vue 3** single-page application built with **Vite 7**, **Vue Router 4**, and **Pinia** (with **pinia-plugin-persistedstate** for auth and client state). It talks to the Laravel backend exclusively over **HTTP JSON** using **Axios**, with a single configurable base URL that must include **`/api/v1`** (no trailing slash). The UI uses **Bootstrap 5**, **Bootstrap Icons**, **Vue Toastification** for feedback, **vue-loading-overlay** for global loading, and components for product zoom, star ratings, and safe HTML rendering (**vue-dompurify-html**).

The app implements a full **e-commerce customer flow**: browse and filter products, product detail with reviews, cart and checkout, **Stripe Checkout** (redirect to Stripe, return to a success route), wishlist, profile and orders, **email verification**, and **Sign in with Google** (OAuth redirect from the API, token handoff on `/auth/google/callback`). Route guards enforce auth for protected pages. **Vitest** with **Vue Test Utils** and **jsdom** drives a broad **component and store** test suite; **ESLint 9** enforces code quality. CI (see the repository `.github/workflows/deploy.yml`) runs **`npm ci` → `npm run lint` → `npm run test:run` → `npm run build`**.

This document explains how to **clone the monorepo, install Node dependencies, configure `VITE_API_BASE_URL`, run the dev server alongside the Laravel API, run tests and coverage, lint, and build for production**—so another developer can run the same stack you do.

**Full HTTP API (paths relative to your `VITE_API_BASE_URL`, cURL, payloads):** [`../docs/API.md`](../docs/API.md).

---

## Table of contents

1. [Prerequisites](#prerequisites)  
2. [Repository layout](#repository-layout)  
3. [Install dependencies](#install-dependencies)  
4. [Environment variables](#environment-variables)  
5. [How the app talks to the API](#how-the-app-talks-to-the-api)  
6. [Run the development server](#run-the-development-server)  
7. [Production build & preview](#production-build--preview)  
8. [Features and routes (user-facing)](#features-and-routes-user-facing)  
9. [State management (Pinia)](#state-management-pinia)  
10. [Google Sign-In (SPA side)](#google-sign-in-spa-side)  
11. [Payments (Stripe)](#payments-stripe)  
12. [Linting](#linting)  
13. [Testing](#testing)  
14. [CI alignment](#ci-alignment)  
15. [Troubleshooting](#troubleshooting)  
16. [Related documentation](#related-documentation)  
17. [Clearing auth / cached client state](#clearing-auth--cached-client-state)  
18. [Monorepo: backend `composer dev` vs this app](#monorepo-backend-composer-dev-vs-this-app)  
19. [Project documentation index (`docs/`)](#project-documentation-index-docs)

---

## Prerequisites

- **Node.js** — CI uses **Node 22** (see `.github/workflows/deploy.yml`). LTS **20+** or **22** is recommended.  
- **npm** (comes with Node; the repo uses `package-lock.json` — use **`npm ci`** in CI and clean installs).  
- **Backend running** — For a full experience, run the Laravel API from `../backend` (see `../backend/README.md`). The default dev API URL is `http://127.0.0.1:8000/api/v1`.  
- **Browser** — Modern evergreen browser; local storage is used for Pinia persistence (auth token).

---

## Repository layout

From the repository root, the SPA lives under **`frontend/`**:

```text
frontend/
  .env.development      # committed; Vite loads this in dev (mode: development)
  .env.production       # may exist for local prod builds; often gitignored for secrets
  .env.production.example
  .env.example          # notes only; real values go in .env.development / .env.production
  index.html
  package.json
  vite.config.js
  eslint.config.js
  src/
    main.js             # axios defaults, Pinia, router, plugins
    config/api.js       # VITE_API_BASE_URL → apiBaseUrl
    router/index.js     # routes and navigation guards
    stores/             # Pinia stores (auth, cart, products, wishlist, …)
    components/         # pages and UI (auth, cart, checkout, payment, profile, …)
    test/setup.js       # Vitest global axios mock
```

---

## Install dependencies

From the **`frontend/`** directory:

```bash
cd frontend
npm install
```

For a **lockfile-frozen** install (same as CI):

```bash
npm ci
```

---

## Environment variables

Vite exposes only variables prefixed with **`VITE_`** to client code.

| File | When used | Purpose |
|------|------------|--------|
| **`.env.development`** | `npm run dev` | Typically sets `VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1` (committed in this repo). |
| **`.env.production`** | `npm run build` | Set `VITE_API_BASE_URL` to your **public API** origin, e.g. `https://your-domain.com/api/v1`. Use **`.env.production.example`** as a template; copy to `.env.production` on the machine that runs the build. |

**Rules (see `src/config/api.js` and `.env.example`):**

- **Must** include the path **`/api/v1`**.  
- **No trailing slash** (the code strips one if present).  
- If `VITE_API_BASE_URL` is empty, requests can break — always set it for real use.

**Example — local development (` .env.development `):**

```env
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1
```

**Example — production build (`.env.production` or your hosting env):**

```env
VITE_API_BASE_URL=https://your-api-host.example.com/api/v1
```

The Laravel backend’s **`FRONTEND_URL`** must match where users open this SPA (origin only, e.g. `https://yoursite.com` or `http://localhost:5173` in dev) for OAuth redirects and email links. Keep **API** (this variable) and **FRONTEND_URL** (backend) consistent with your deployment.

---

## How the app talks to the API

- **`src/config/api.js`** exports `apiBaseUrl` from `import.meta.env.VITE_API_BASE_URL`.  
- **`src/main.js`** sets:

  - `axios.defaults.baseURL = apiBaseUrl`  
  - `Accept: application/json`  
  - `Content-Type: application/json`  
  - After Pinia bootstraps, **`useAuthStore().initializeAxiosHeaders()`** sets `Authorization: Bearer <token>` if a persisted token exists.

- All store and component calls use **relative paths** (e.g. `axios.get('/products')`, `axios.post('/user/login', …)`) so they are resolved against **`VITE_API_BASE_URL`** (which already includes `/api/v1`).

- The backend’s JSON shape (`message`, `data`, `error`, `status` where used) is documented in the backend README and `.cursor/rules/laravel-api-responses.mdc`. The auth store reads fields such as `response.data.user`, `response.data.access_token` on login, and normalizes some register responses — **the Laravel controllers are the source of truth** for response bodies.

- **CORS / cookies:** this SPA primarily uses **Bearer tokens**, not session cookies, for the JSON API. Google OAuth is initiated on the **Laravel** side; the API redirects back to this app with a **query `token`**.

---

## Run the development server

**Terminal 1 — Laravel (from repo root):**

```bash
cd backend
php artisan serve
# default: http://127.0.0.1:8000
```

**Terminal 2 — Vite (from `frontend/`):**

```bash
npm run dev
```

Vite usually serves the app at **`http://localhost:5173`** (see terminal output). Ensure **`.env.development`** points `VITE_API_BASE_URL` at the same host/port as `php artisan serve` with `/api/v1` appended.

Open the printed local URL in the browser. For **Google login** and **Stripe return URLs**, the backend and this `FRONTEND_URL` / success URLs must match how you access the app (localhost vs 127.0.0.1 can matter for Google Console redirect rules if you mix them).

**Optional — queue worker (backend):** order confirmation and some emails are queued on the server; run `php artisan queue:work` in the backend if you need those jobs processed during dev (see `../backend/README.md`).

---

## Production build & preview

**Build** (uses `.env.production` when present):

```bash
npm run build
```

Output goes to **`dist/`** (Vite default). The deploy pipeline uploads this folder to the server; **the droplet does not run `npm run build`**.

**Preview the built app locally:**

```bash
npm run preview
```

Point **`VITE_API_BASE_URL` in the build** to the **public** API URL your users will call.

---

## Features and routes (user-facing)

Defined in **`src/router/index.js`**. Guards: **`beforeEnter: checkIfUserIsLoggedIn`** redirects unauthenticated users to `/login` (profile, checkout, orders, wishlist, success payment). **`checkIfUserIsLoggedOut`** keeps logged-in users off login/register.

| Path | Description |
|------|-------------|
| `/` | Home — product listing and filters. |
| `/login`, `/register` | Email/password auth (guests only). |
| `/auth/google/callback` | Google OAuth return — reads `token` or `error` from query, updates auth store, syncs cart. |
| `/email/verify` | Email verification link handling. |
| `/about` | About page. |
| `/product/:slug` | Product detail, images, add to cart, reviews. |
| `/cart` | Cart. |
| `/checkout` | Checkout (auth required). |
| `/success/payment/:hash` | Post-Stripe return; completes order flow (auth required). |
| `/user/orders`, `/user/wishlist` | Orders and wishlist (auth). |
| `/profile` | User profile (auth). |
| `*` | 404 — `NotFound` component. |

**Administration** of the catalog is **not** in this SPA — it is the Laravel **Blade admin** at `{APP_URL}/admin` (see `../backend/README.md`).

---

## State management (Pinia)

Stores under **`src/stores/`** include (non-exhaustive): **`useAuthStore`**, **`useCartStore`**, **`useProductsStore`**, **`useProductDetailsStore`**, **`useWishlistStore`**, and related modules. **Auth** and other critical state use **`persist: true`** so the session survives reloads; the auth token is re-applied to Axios in **`initializeAxiosHeaders`**.

---

## Google Sign-In (SPA side)

1. The user starts Google login from the UI; the **browser** is sent to the Laravel routes **`/api/v1/auth/google`** → Google → **`/api/v1/auth/google/callback`**.  
2. Laravel issues a Sanctum token and **redirects** to:

   `FRONTEND_URL/auth/google/callback?token=...`

3. **`GoogleCallback.vue`** reads the token, calls **`authStore.setToken`**, then **`getLoggedInUser`**, refreshes the cart, and redirects home.

**Requirements:** `GOOGLE_*` and `FRONTEND_URL` on the backend must match this flow; the redirect URI in Google Cloud Console must match the Laravel callback URL exactly.

---

## Payments (Stripe)

- **`Stripe.vue`** (checkout) calls **`POST /orders/pay`** with `success_url`, `cancel_url`, and cart line items. The API returns a Stripe Checkout **URL**; the app navigates the browser there.  
- After payment, Stripe sends the user to the success URL with a session id; the success page logic coordinates with the API to create orders as designed (see `SuccessPayment.vue` and backend `OrderController`).

**Keys:** `STRIPE_SECRET_KEY` / `STRIPE_PUBLIC_KEY` are **server-only** and **not** in Vite; only the public key is needed in the API responses the SPA consumes if the backend exposes it for Stripe.js—this project’s checkout is largely **server-created Checkout Session** + redirect, so the critical configuration is on Laravel.

---

## Linting

```bash
npm run lint
```

Auto-fix where rules allow:

```bash
npm run lint:fix
```

Configuration: **`eslint.config.js`** (flat config), with Vue and Vitest plugins.

---

## Testing

**Watch mode (TDD):**

```bash
npm run test
```

**CI-style single run:**

```bash
npm run test:run
```

**Coverage** (V8 provider):

```bash
npm run test:coverage
```

Reports go under **`coverage/`** (see `.gitignore` — `coverage` is ignored).

- **Pattern:** `src/**/*.{test,spec}.{js,ts,vue}` (see `vite.config.js` → `test.include`).  
- **Setup:** `src/test/setup.js` **mocks `axios` globally** to avoid real HTTP in tests; individual specs can override with `vi.mocked(axios.…).mockResolvedValue(…)`.  
- **Environment:** `jsdom` for DOM APIs.

The suite includes app shell, **Navbar**, **Sidebar**, home and product views, **auth** (Login, Register, VerifyEmail, **GoogleCallback**), **cart**, **checkout**, **Stripe**, **SuccessPayment**, **profile** and **orders**/**wishlist** components, **stores**, and more (~40 spec files).

---

## CI alignment

The workflow **“CI (backend + frontend)”** in `.github/workflows/deploy.yml` runs, for the frontend:

```bash
npm ci
npm run lint
npm run test:run
npm run build
```

Reproduce locally from **`frontend/`** after `npm ci` to match the pipeline. Release tags also upload `frontend/dist` for deployment.

---

## Troubleshooting

| Symptom | Things to check |
|--------|-------------------|
| **API calls 404 or wrong path** | `VITE_API_BASE_URL` must end with `/api/v1` (no trailing slash). Restart `npm run dev` after env changes. |
| **CORS errors** | Usually misaligned origins; for Bearer-based API, ensure backend `SANCTUM` / CORS config allows your SPA origin if you use cookies — this app mostly uses **Bearer** tokens. |
| **Login works in Postman but not SPA** | Check `VITE_API_BASE_URL`; mixed `localhost` vs `127.0.0.1` vs https. |
| **Google redirect fails** | Backend `FRONTEND_URL` and Google Console redirect must match; callback must hit Laravel, then redirect to this app’s `/auth/google/callback`. |
| **Blank API responses** | Backend down or wrong port; open Network tab and confirm request URL. |
| **Tests fail after axios changes** | Update mocks in `src/test/setup.js` or the spec’s `vi.mocked` overrides. |
| **Build uses wrong API URL** | Ensure `.env.production` (or your host’s env) sets `VITE_API_BASE_URL` **at build time** — Vite inlines `VITE_*` at build, not at runtime. |
| **Still “logged in” after logout or odd auth** | The auth store is **persisted** (localStorage). Clear site data for the dev origin or remove the storage key; hard refresh (see below). |

---

## Clearing auth / cached client state

**Pinia** (`useAuthStore`) uses **`pinia-plugin-persistedstate`**, so the token and user can survive a normal refresh. If you are debugging login/logout or hitting stale 401s:

- **Chrome / Edge:** DevTools → **Application** → **Storage** → “Clear site data” for `http://localhost:5173` (or your dev origin), or delete **Local Storage** keys for the site.  
- **Firefox:** **Storage** inspector → clear local storage for the origin.  
- **Quick test in console:** `localStorage.clear()` (only for that tab’s origin) then reload.

After changing `VITE_API_BASE_URL`, **stop and restart** `npm run dev` so Vite reloads env.

---

## Monorepo: backend `composer dev` vs this app

The **`backend/`** folder has a Composer script **`dev`** that runs `php artisan serve`, queue, Pail, and **`npm run dev`** in **`backend/`** (Laravel’s own Vite/tailwind tooling). That is **not** the same process as the **Vue 3** storefront in this **`frontend/`** package.

- **Run the customer SPA:** from **`frontend/`** → `npm run dev` (with the Laravel API from `../backend` running separately, unless you use a process manager to start both).  
- **Two terminals (typical):** Terminal 1: `../backend` → `php artisan serve` (and `queue:work` if needed). Terminal 2: **`frontend/`** → `npm run dev`.

---

## Project documentation index (`docs/`)

| Document | What it is |
|----------|------------|
| [`../docs/API.md`](../docs/API.md) | Every **`/api/v1`** route plus **`GET /up`**, **Sanctum CSRF (optional)**, and **admin** HTML routes — matches how Axios calls the backend. |
| [`../docs/CICD.md`](../docs/CICD.md) | **CI**, tags, and production deploy. |

---

## Related documentation

- **Monorepo overview (start here):** [`../README.md`](../README.md)  
- **[`docs/` index](#project-documentation-index-docs)** — `API.md`, `CICD.md`  
- **HTTP API (detail):** [`../docs/API.md`](../docs/API.md)  
- **Backend (Laravel API, admin, jobs, Stripe, Google, tests):** [`../backend/README.md`](../backend/README.md)  
- **Deployment / GitHub Actions:** [`.github/workflows/deploy.yml`](../.github/workflows/deploy.yml)  
- **Releases & CI/CD:** [`../docs/CICD.md`](../docs/CICD.md)  
- **Frontend conventions (optional):** [`.cursor/rules/frontend.mdc`](../.cursor/rules/frontend.mdc)

Together, the **Vue SPA** and **Laravel API** form one product: the frontend focuses on the **customer** experience and **API consumption**; the backend owns **data, auth, payments, and operations**.
