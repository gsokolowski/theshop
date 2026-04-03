#!/usr/bin/env bash
# Deploy Vite frontend: sync repo to origin/main, build from repo/frontend, publish dist/ to server web root.
# Repo layout: this file lives at <repo>/scripts/deploy-frontend.sh
# On server: bash /var/www/the-shop/repo/scripts/deploy-frontend.sh
# Pairs with scripts/deploy-backend.sh (same REPO discovery).
#
# Requires Node.js 20+ on the server (Vite 7 / @vitejs/plugin-vue 6). Ubuntu's default nodejs
# package may be too old; use NodeSource or another install that puts `node` on PATH.
set -euo pipefail

export PATH="/usr/local/bin:/usr/bin:/bin:${PATH:-}"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO="$(cd "${SCRIPT_DIR}/.." && pwd)"
DEST="/var/www/the-shop/frontend"
FRONTEND="${REPO}/frontend"

if [[ ! -d "${REPO}/.git" ]]; then
  echo "ERROR: ${REPO} is not a git repository."
  echo "Create it with: git clone <your-repo-url> ${REPO}"
  exit 1
fi

if [[ ! -d "${DEST}" ]]; then
  echo "ERROR: ${DEST} does not exist. Create it once, e.g.: sudo mkdir -p ${DEST} && sudo chown www-data:www-data ${DEST}"
  exit 1
fi

command -v npm >/dev/null 2>&1 || { echo "ERROR: npm is not in PATH."; exit 1; }
command -v node >/dev/null 2>&1 || { echo "ERROR: node is not in PATH."; exit 1; }

NODE_MAJOR="$(node -p "parseInt(process.versions.node.split('.')[0], 10)" 2>/dev/null || echo 0)"
if [[ "${NODE_MAJOR}" -lt 20 ]]; then
  echo "ERROR: Node.js 20 or newer is required (Vite 7). Found: $(node -v 2>/dev/null || echo unknown)."
  echo "On Ubuntu, e.g.: curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && apt-get install -y nodejs"
  exit 1
fi

echo "==> git sync to origin/main (no local drift)"
cd "${REPO}"
git fetch origin
git checkout main
git reset --hard origin/main

if [[ ! -f "${FRONTEND}/package.json" ]]; then
  echo "ERROR: ${FRONTEND}/package.json not found."
  exit 1
fi

# Vite reads .env.production.local at build time; keep it only on the server (not in git).
if [[ -f "${DEST}/.env.production.local" ]]; then
  cp -a "${DEST}/.env.production.local" "${FRONTEND}/.env.production.local"
  echo "==> copied ${DEST}/.env.production.local -> ${FRONTEND}/.env.production.local for build"
fi

echo "==> npm ci && npm run build in ${FRONTEND}"
cd "${FRONTEND}"
npm ci
npm run build

if [[ ! -d "${FRONTEND}/dist" ]]; then
  echo "ERROR: Build did not produce ${FRONTEND}/dist"
  exit 1
fi

echo "==> rsync dist/ -> ${DEST}/"
rsync -a --delete "${FRONTEND}/dist/" "${DEST}/"

echo "==> permissions"
chown -R www-data:www-data "${DEST}"

echo "==> deploy-frontend.sh finished OK"
