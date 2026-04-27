# Release workflow and CI/CD

This document describes how **GitHub Actions** integrates with **releases** and **production** for this repository, and how it matches the workflow file **`.github/workflows/deploy.yml`**.

---

## Current model (what the repo does today)

| Piece | Behavior |
|-------|------------|
| **Default branch** | `main` — integration and release line. |
| **CI** | Runs on **every pull request to `main`**, on **every push to `main`**, and on **every push of a version tag** matching `v*` (e.g. `v1.2.3`). |
| **Production deploy** | **Only** when a tag `v*` is **pushed**. There is **no** automatic deploy on push to `main` alone. |
| **Staging** | **Not** defined in the current workflow file. If you add a `stage` branch and a staging server later, extend the workflow or add a second workflow. |

Optional day-to-day branching (e.g. `feature/*` → PR to `main`) is a team choice; the pipeline only cares about **`main`** and **`v*`** tags as implemented.

---

## Release: tag → production

1. Merge your work into **`main`** (via PR or direct push, per your team rules).
2. On your machine, create an **annotated** tag at the commit you want in production:
   ```bash
   git checkout main
   git pull origin main
   git tag -a v1.2.3 -m "Release 1.2.3"
   git push origin v1.2.3
   ```
3. **Pushing the tag** triggers the workflow: **full CI** (backend + frontend), then **deploy** to the **DigitalOcean droplet** (see below).

**Rollback idea (same as workflow comments):** point a new tag at an older good commit and push, e.g. `git tag v1.2.4 v1.2.2 && git push origin v1.2.4`.

---

## Workflow name and file

- **Name:** `CI and deploy`  
- **File:** `.github/workflows/deploy.yml`  
- **Concurrency:** one run per ref; newer runs cancel older in-progress runs on the same ref (`concurrency.group`).

---

## What the CI job runs

**Job id:** `ci` — **“CI (backend + frontend)”** — `runs-on: ubuntu-latest`, timeout 30 minutes.

| Step | What happens |
|------|----------------|
| Checkout | `actions/checkout@v5` |
| PHP | **PHP 8.4** via `shivammathur/setup-php@v2` with extensions: `mbstring`, `dom`, `fileinfo`, `pdo_sqlite`, `tokenizer`, `xml`, `ctype`, `json`, `openssl`, `curl` — **no** coverage extension in CI. |
| Backend (`backend/`) | `cp -f .env.testing .env` → `composer install` → `composer phpstan` → `php artisan test` |
| Node | **Node 22** via `actions/setup-node@v5`, npm cache on `frontend/package-lock.json` |
| Frontend (`frontend/`) | `npm ci` → `npm run lint` → `npm run test:run` → `npm run build` |
| Artifact (tags only) | If the event is **`push`** and the ref is a **`refs/tags/v*`** tag: **upload** `frontend/dist` as artifact **`frontend-dist`** (`actions/upload-artifact@v7`). |

**Pull requests and pushes to `main` without a tag:** same backend + frontend steps run, but **no** `frontend-dist` artifact and **no** deploy job.

---

## What the deploy job runs (tags only)

**Job id:** `deploy` — **“Deploy to droplet”** — runs **only** when:

- `github.event_name == 'push'` **and**
- the ref is **`refs/tags/v*`** (e.g. `v1.0.0`).

It **needs** a successful `ci` job, then:

1. **Download** artifact `frontend-dist` (`actions/download-artifact@v8`) into `frontend-dist/`.
2. **Require** repository **Actions secrets** (must exist, names exact):
   - `SSH_PRIVATE_KEY` — private key for SSH to the server (full PEM, validated by `ssh-keygen -y`).
   - `SSH_HOST` — droplet hostname or IP (trimmed).
   - `SSH_USER` — SSH user (trimmed).
3. **SSH setup** — write key to `~/.ssh/deploy_key`, `ssh-keyscan` host into `known_hosts`, set `SSH_DEPLOY_HOST` / `SSH_DEPLOY_USER` in `GITHUB_ENV`.
4. **Verify SSH** — non-interactive `ssh` echo test to the host.
5. **Remote preflight** — on the server: user/hostname, presence of `scripts/deploy-backend.sh` under the repo path, `git`, `rsync`, `bash`, `php8.4` or `php`, `composer` (e.g. `/usr/local/bin/composer`), etc.
6. **Deploy backend** — SSH and run (no login shell, to avoid `.profile` / `.bashrc` breaking non-interactive runs):
   ```text
   bash /var/www/the-shop/repo/scripts/deploy-backend.sh
   ```
   with `DEPLOY_REF` set to the **tag name** (e.g. `v1.2.3`) so the server checks out that ref in the clone under `/var/www/the-shop/repo` (see `scripts/deploy-backend.sh`).

7. **Publish frontend** — **rsync** the **prebuilt** `frontend-dist/` directory to the droplet at:
   ```text
   /var/www/the-shop/frontend/
   ```
   (with `--delete`), then `chown -R www-data:www-data` on that path.

**Important:** the droplet does **not** run `npm run build`. The **Vite** build runs only in GitHub Actions; production static files are the **artifact** from `npm run build` with whatever **`VITE_*`** were baked in at build time. To change the API URL for the built SPA, set **`VITE_API_BASE_URL`** in **`frontend/.env.production`** (or a CI step that creates `.env.production` from secrets) **before** `npm run build` in the workflow. The inline comment in the workflow file notes this.

---

## Event matrix (actual behavior)

| Event | PHPStan + backend tests | Frontend lint, tests, build | Upload `dist` artifact | Deploy (SSH + backend script + rsync frontend) |
|--------|-------------------------|-----------------------------|-------------------------|-----------------------------------------------|
| **PR** to `main` | Yes | Yes | No | No |
| **Push** to `main` | Yes | Yes | No | No |
| **Push** tag `v*` | Yes | Yes | Yes | Yes |

There is **no** deploy on **push to `main`** without a **tag**.

---

## GitHub repository settings (recommended)

- **Branch protection** on `main`: require PR, require **CI and deploy** / `ci` job to pass before merge, where practical.
- **Secrets:** add **`SSH_PRIVATE_KEY`**, **`SSH_HOST`**, **`SSH_USER`** under **Settings → Secrets and variables → Actions → Repository secrets** (or organization-level equivalents). The workflow does not use `STAGING_HOST` / `PRODUCTION_HOST` names; it uses the three names above.
- **Tags:** restrict who can push tags if you want releases gated.

---

## Server layout (referenced by workflow and scripts)

- Repo clone: e.g. **`/var/www/the-shop/repo`** (contains `scripts/deploy-backend.sh` and the git checkout).
- Laravel app on server: **`/var/www/the-shop/backend`**
- Public static frontend: **`/var/www/the-shop/frontend/`** (content rsynced from CI artifact; owned by `www-data` after deploy)

Adjust paths in your server docs if your droplet uses different directories — the workflow and `deploy-backend.sh` must stay consistent.

---

## Hotfixes

Typical flow: fix on a branch, PR to `main`, merge, then **tag a new patch** (e.g. `v1.2.1`) and `git push origin v1.2.1` to deploy.

---

## Related

- **Workflow source of truth:** `.github/workflows/deploy.yml` — if this document and the YAML disagree, **trust the YAML** and update this file.
- **Backend deploy script:** `scripts/deploy-backend.sh`
- **Backend / frontend local setup:** `backend/README.md`, `frontend/README.md`
- **Credentials:** real keys and hostnames belong in **secrets** and server `.env` — never commit them.

### Optional: develop / stage branches

An older version of this document described **`develop`**, **`stage`**, and deploy-on-push to staging. The **current** workflow is **`main` + `v*`** only. You can reintroduce staging by adding `on.push.branches: [stage]` and a second deploy target, or a separate workflow, and update this document again when that exists.
