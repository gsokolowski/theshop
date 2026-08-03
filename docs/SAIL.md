# Local development with Laravel Sail

This project uses **Laravel Sail** (Docker Compose) plus a **custom nginx** gateway so local URLs match production path layout on one hostname.

| URL | Serves |
|-----|--------|
| `https://shop-local.codecreators.co.uk/` | Vue 3 SPA (Vite on the **host** by default) |
| `https://shop-local.codecreators.co.uk/api/...` | Laravel API |
| `https://shop-local.codecreators.co.uk/admin/...` | Blade admin |
| `https://shop-local.codecreators.co.uk/horizon/...` | Horizon (queue dashboard) |
| `https://shop-local.codecreators.co.uk/storage/...` | Public uploads |
| `http://localhost:8025` | Mailpit (captured mail UI) |

PHP (Laravel), Horizon, MySQL, Redis, Mailpit, and nginx run via `backend/compose.yaml`. Vite usually runs on the host so Docker Desktop does not need to file-share paths under `/Applications` (common with XAMPP).

## Prerequisites

- Docker Desktop (or Docker Engine + Compose v2)
- **Docker Desktop → Settings → Resources → File Sharing** must include this repo’s parent path. If the project lives under XAMPP (`/Applications/XAMPP/...`), add **`/Applications`** (or the full project path), then Apply & Restart. Without this, mounts fail with `mounts denied`.
- Node.js 20+ on the host (for `frontend/`)
- Git; PHP/Composer on the host only if you need `composer install` before the first Sail build

## One-time setup

1. **Hosts file** — add:

   ```text
   127.0.0.1 shop-local.codecreators.co.uk
   ```

   macOS/Linux: `/etc/hosts`. Windows: `C:\Windows\System32\drivers\etc\hosts`.

2. **TLS certs** (required — nginx listens on 443):

   ```bash
   chmod +x scripts/generate-local-certs.sh
   ./scripts/generate-local-certs.sh
   ```

   Prefer [mkcert](https://github.com/FiloSottile/mkcert) if installed (trusted in the browser). Otherwise OpenSSL self-signed certs are created and the browser will warn once.

3. **Backend env** — from `backend/`:

   ```bash
   cp .env.example .env   # if you do not already have .env
   # Ensure Sail-oriented values (see .env.example): APP_URL, DB_*, REDIS_HOST=redis, FRONTEND_URL, etc.
   ```

4. **Start Sail** — from `backend/`:

   ```bash
   ./vendor/bin/sail build
   ./vendor/bin/sail up -d --remove-orphans
   ./vendor/bin/sail artisan key:generate
   ./vendor/bin/sail artisan migrate --seed
   ./vendor/bin/sail artisan storage:link
   ```

5. **Start Vite on the host** — from `frontend/`:

   ```bash
   npm install
   npm run dev
   ```

   `frontend/.env.development` should use `VITE_API_BASE_URL=https://shop-local.codecreators.co.uk/api/v1` and the `VITE_HMR_*` values so HMR works through the nginx TLS proxy.

6. Open **https://shop-local.codecreators.co.uk** (accept the cert warning if self-signed).

## Day-to-day

```bash
cd backend
./vendor/bin/sail up -d          # start Docker services
./vendor/bin/sail down           # stop
./vendor/bin/sail artisan …      # any artisan command
./vendor/bin/sail test           # PHPUnit
```

In another terminal: `cd frontend && npm run dev`.

Horizon runs as the `horizon` Compose service. Mailpit UI: http://localhost:8025.

MySQL from the host (TablePlus, etc.): `127.0.0.1:33060` (user/password from `.env`, default `sail` / `password`). Redis from the host: `127.0.0.1:6380` (avoids clashing with another Redis on `6379`).

Stop any other container/process bound to host ports **80** and **443** before `sail up`.

## Optional: Vite inside Docker

If Docker Desktop → **Settings → Resources → File Sharing** includes this repository path (e.g. add `/Applications` when using XAMPP), you can run:

```bash
# Point nginx at the frontend container instead of the host — edit
# backend/docker/nginx/default.conf upstream vite to: server frontend:5173;
./vendor/bin/sail --profile docker-frontend up -d
```

Then you do not need `npm run dev` on the host.

## Frontend API URL

With the gateway, the SPA calls the API on the **same host**:

```text
VITE_API_BASE_URL=https://shop-local.codecreators.co.uk/api/v1
```

For host-only `php artisan serve` + `npm run dev` (no Sail), use `http://127.0.0.1:8000/api/v1` and remove `VITE_HMR_*` from `.env.development`.

## Google OAuth / Stripe

Add authorized redirect URI in Google Cloud:

```text
https://shop-local.codecreators.co.uk/api/v1/auth/google/callback
```

Keep Stripe test keys in `backend/.env` as before.

## Without Sail

You can still use SQLite + `php artisan serve` + `npm run dev` as described in the root README. Stop Sail first (`./vendor/bin/sail down`) so ports `80`/`443` are free, and adjust `.env` / `VITE_API_BASE_URL` accordingly.

## Production

Production remains the DigitalOcean droplet + host nginx (`backend/nginx-site.example.conf`). Sail is for **local** development only.
