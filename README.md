# The Shop

A full-stack e-commerce monorepository: a **Laravel 12** backend (REST API `/api/v1`, session admin, jobs, Stripe, Google OAuth) and a **Vue 3 + Vite** storefront, with **CI** on every push/PR to `main` and **production deploy** on **version tags** `v*`.

## Repository layout

| Path | What it is |
|------|------------|
| [`backend/`](backend/) | Laravel app: JSON API, Blade admin, queues, tests, PHPStan. |
| [`frontend/`](frontend/) | Vue 3 SPA: cart, checkout, profile, Vitest, ESLint. |
| [`docs/`](docs/) | **API**, **CI/CD**, and cross-cutting technical docs. |
| [`.github/workflows/`](.github/workflows/) | GitHub Actions (see [`docs/CICD.md`](docs/CICD.md)). |
| [`scripts/`](scripts/) | Deploy and server-side helpers (e.g. `deploy-backend.sh`). |

## Quick start (local development)

**Recommended:** [Laravel Sail + nginx gateway](docs/SAIL.md) — one hostname like production:

| URL | Serves |
|-----|--------|
| `https://shop-local.codecreators.co.uk/` | Vue SPA |
| `https://shop-local.codecreators.co.uk/api/...` | Laravel API |
| `https://shop-local.codecreators.co.uk/admin/...` | Blade admin |

```bash
# /etc/hosts: 127.0.0.1 shop-local.codecreators.co.uk
./scripts/generate-local-certs.sh
cd backend
cp .env.example .env   # if needed
./vendor/bin/sail up -d --remove-orphans
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
cd ../frontend && npm install && npm run dev
```

Then open `https://shop-local.codecreators.co.uk`. Full Sail details: [`docs/SAIL.md`](docs/SAIL.md).

### Alternative: host PHP / Node (no Docker)

**Requirements:** PHP 8.2+ with Composer, Node.js 20+ (CI uses 22), Git. Adjust `.env` for SQLite/`127.0.0.1` Redis and set `VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1`.

1. **Clone**

   ```bash
   git clone <repository-url> the-shop
   cd the-shop
   ```

2. **Backend** — from `backend/`:

   ```bash
   cp .env.example .env
   php artisan key:generate
   # Point DB_* at SQLite or local MySQL; REDIS_HOST=127.0.0.1 if using host Redis
   php artisan migrate
   php artisan db:seed
   php artisan storage:link
   php artisan serve
   ```

   Optional: `php artisan horizon` (or `queue:work`) for queued mail.

3. **Frontend** — from `frontend/`:

   ```bash
   npm install
   npm run dev
   ```

4. **Open** Vite (usually `http://localhost:5173`) and the API at `http://127.0.0.1:8000`. Admin: `http://127.0.0.1:8000/admin`.

**Stripe, Google sign-in, and real mail** need keys in `backend/.env` — see [`backend/README.md`](backend/README.md).

## Documentation

| Document | Description |
|----------|-------------|
| [Backend setup & features](backend/README.md) | `.env`, migrate/seed, admin, jobs, mail, Stripe, Google, tests, PHPStan. |
| [Frontend setup & build](frontend/README.md) | Vite, `VITE_API_BASE_URL`, routes, tests, lint, CI. |
| [HTTP API reference](docs/API.md) | All routes (`/api/v1`, health, optional Sanctum, admin table). |
| [CI/CD & releases](docs/CICD.md) | GitHub Actions, tag deploy, droplet, secrets. |
| [Sail / local Docker](docs/SAIL.md) | Sail + nginx gateway (`shop-local.codecreators.co.uk`). |
| [PREFERENCES.md](PREFERENCES.md) | Index of Cursor rules and conventions. |

## Architecture (short)

- **Storefront (Vue)** talks to the **Laravel API** with `Authorization: Bearer` (Sanctum) and `VITE_API_BASE_URL` including `/api/v1`.
- **Admin** is server-rendered Blade under `/admin` (separate from the JSON API).
- **Production** builds the SPA in CI, rsyncs `dist/` to the server; the backend is deployed from the same tag via `scripts/deploy-backend.sh` — see [docs/CICD.md](docs/CICD.md).

For deeper detail, use the [backend](backend/README.md) and [frontend](frontend/README.md) READMEs and [docs/API.md](docs/API.md).
