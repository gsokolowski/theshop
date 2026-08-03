#!/usr/bin/env bash
# Self-signed TLS certs for https://shop-local.codecreators.co.uk (Sail nginx).
# Prefer mkcert if installed: mkcert -cert-file ... -key-file ... shop-local.codecreators.co.uk
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CERT_DIR="$ROOT/backend/docker/nginx/certs"
CERT="$CERT_DIR/shop-local.codecreators.co.uk.pem"
KEY="$CERT_DIR/shop-local.codecreators.co.uk-key.pem"

mkdir -p "$CERT_DIR"

if [[ -f "$CERT" && -f "$KEY" ]]; then
  echo "Certs already exist in $CERT_DIR"
  exit 0
fi

if command -v mkcert >/dev/null 2>&1; then
  echo "Generating trusted local certs with mkcert..."
  mkcert -cert-file "$CERT" -key-file "$KEY" shop-local.codecreators.co.uk localhost 127.0.0.1
else
  echo "mkcert not found; generating self-signed OpenSSL certs (browser will warn)..."
  openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
    -keyout "$KEY" \
    -out "$CERT" \
    -subj "/CN=shop-local.codecreators.co.uk" \
    -addext "subjectAltName=DNS:shop-local.codecreators.co.uk,DNS:localhost,IP:127.0.0.1"
fi

echo "Wrote:"
echo "  $CERT"
echo "  $KEY"
