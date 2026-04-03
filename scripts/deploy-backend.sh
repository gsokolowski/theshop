#!/usr/bin/env bash
# Deploy Laravel backend from repo (main) into /var/www/the-shop/backend.
# Repo layout: this file lives at <repo>/scripts/deploy-backend.sh
# On server: bash /var/www/the-shop/repo/scripts/deploy-backend.sh
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO="$(cd "${SCRIPT_DIR}/.." && pwd)"
DEST="/var/www/the-shop/backend"
PHP_BIN="${PHP_BIN:-php8.4}"

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

echo "==> permissions"
chown -R www-data:www-data "${DEST}/storage" "${DEST}/bootstrap/cache"

echo "==> composer + artisan"
cd "${DEST}"
export COMPOSER_ALLOW_SUPERUSER=1
"${PHP_BIN}" /usr/local/bin/composer install --no-dev --no-interaction --optimize-autoloader
"${PHP_BIN}" artisan migrate --force
"${PHP_BIN}" artisan config:cache
"${PHP_BIN}" artisan route:cache
"${PHP_BIN}" artisan view:cache

echo "==> deploy-backend.sh finished OK"
