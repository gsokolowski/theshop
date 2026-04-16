#!/usr/bin/env bash
# Deploy Laravel backend from repo (main) into /var/www/the-shop/backend.
# Repo layout: this file lives at <repo>/scripts/deploy-backend.sh
# On server: bash /var/www/the-shop/repo/scripts/deploy-backend.sh
#
# Creates public/storage -> storage/app/public so uploaded files (e.g. profile images) are
# reachable at /storage/... . Without this, PUT /user/profile/update may succeed but images 404.
#
# chmod 775 on storage + bootstrap/cache matches Laravel deploy docs so www-data can write
# uploads (storage/app/public), logs, framework views cache, etc.
set -euo pipefail

# Non-interactive SSH (e.g. GitHub Actions) often has a minimal PATH; match deploy-frontend.sh.
export PATH="/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:${PATH:-}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO="$(cd "${SCRIPT_DIR}/.." && pwd)"
DEST="/var/www/the-shop/backend"

if [[ -n "${PHP_BIN:-}" ]]; then
  :
elif command -v php8.4 >/dev/null 2>&1; then
  PHP_BIN="php8.4"
elif command -v php >/dev/null 2>&1; then
  PHP_BIN="php"
else
  echo "ERROR: No php8.4 or php in PATH (PATH=${PATH})"
  exit 1
fi

if [[ -n "${COMPOSER_BIN:-}" ]]; then
  :
elif [[ -x /usr/local/bin/composer ]]; then
  COMPOSER_BIN="/usr/local/bin/composer"
elif command -v composer >/dev/null 2>&1; then
  COMPOSER_BIN="$(command -v composer)"
else
  echo "ERROR: composer not found (tried /usr/local/bin/composer and PATH)"
  exit 1
fi

if [[ ! -d "${REPO}/.git" ]]; then
  echo "ERROR: ${REPO} is not a git repository."
  echo "Create it with: git clone https://github.com/gsokolowski/theshop.git ${REPO}"
  exit 1
fi

if [[ ! -d "${DEST}" ]]; then
  echo "ERROR: ${DEST} does not exist."
  exit 1
fi

echo "==> git sync to origin/main (no local drift)"
cd "${REPO}"
git fetch origin
git checkout main
git reset --hard origin/main

# rsync --delete removes files on dest that are not in source; .env is not in git, so restore it after.
ENV_BACKUP=""
if [[ -f "${DEST}/.env" ]]; then
  ENV_BACKUP="$(mktemp)"
  cp -a "${DEST}/.env" "${ENV_BACKUP}"
  echo "==> backed up .env to temp"
fi

echo "==> rsync repo/backend -> ${DEST}"
rsync -a --delete \
  --exclude=.env \
  --exclude=vendor \
  --exclude=node_modules \
  --exclude=storage/app/public \
  "${REPO}/backend/" "${DEST}/"

if [[ -n "${ENV_BACKUP}" && -f "${ENV_BACKUP}" ]]; then
  cp -a "${ENV_BACKUP}" "${DEST}/.env"
  rm -f "${ENV_BACKUP}"
  echo "==> restored .env"
fi

echo "==> composer + artisan"
cd "${DEST}"
export COMPOSER_ALLOW_SUPERUSER=1
"${PHP_BIN}" "${COMPOSER_BIN}" install --no-dev --no-interaction --optimize-autoloader
"${PHP_BIN}" artisan migrate --force

echo "==> storage link (public/storage -> storage/app/public for uploads)"
mkdir -p "${DEST}/storage/app/public"
"${PHP_BIN}" artisan storage:link --force

"${PHP_BIN}" artisan config:cache
"${PHP_BIN}" artisan route:cache
"${PHP_BIN}" artisan view:cache

echo "==> restart queue worker (Supervisor)"
if command -v supervisorctl >/dev/null 2>&1; then
  if sudo supervisorctl restart 'the-shop-queue:*'; then
    echo "==> queue worker restarted"
  else
    echo "WARN: supervisorctl restart failed; install supervisor and register backend/deploy/supervisor-queue.conf"
  fi
else
  echo "WARN: supervisorctl not in PATH; queue worker not restarted"
fi

echo "==> permissions (775 + www-data: uploads, logs, framework cache)"
mkdir -p \
  "${DEST}/storage/app/public" \
  "${DEST}/storage/framework/cache/data" \
  "${DEST}/storage/framework/sessions" \
  "${DEST}/storage/framework/views" \
  "${DEST}/storage/logs"
chmod -R 775 "${DEST}/storage" "${DEST}/bootstrap/cache"
chown -R www-data:www-data "${DEST}/storage" "${DEST}/bootstrap/cache"

echo "==> deploy-backend.sh finished OK"
