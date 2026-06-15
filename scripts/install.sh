#!/usr/bin/env bash
# One-click native install (Linux/macOS).
# Prerequisites: PHP 8.3+, Composer, Node 20+, PostgreSQL client extensions.

set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> Checking prerequisites"
command -v php >/dev/null || { echo "PHP not found"; exit 1; }
command -v composer >/dev/null || { echo "Composer not found"; exit 1; }
command -v node >/dev/null || { echo "Node.js not found"; exit 1; }
command -v npm >/dev/null || { echo "npm not found"; exit 1; }

PHP_VERSION="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"
if ! php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);'; then
  echo "PHP 8.3+ required, found ${PHP_VERSION}"
  exit 1
fi

NODE_MAJOR="$(node -p "process.versions.node.split('.')[0]")"
if [ "${NODE_MAJOR}" -lt 20 ]; then
  echo "Node.js 20+ required"
  exit 1
fi

for ext in pdo pdo_pgsql mbstring openssl tokenizer xml ctype json bcmath; do
  php -m | grep -qi "^${ext}$" || { echo "Missing PHP extension: ${ext}"; exit 1; }
done

echo "==> Environment file"
if [ ! -f .env ]; then
  if [ -f .env.production.example ]; then
    cp .env.production.example .env
  else
    cp .env.example .env
  fi
  echo "Created .env"
fi

echo "==> PHP dependencies"
composer install --no-interaction --prefer-dist

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force
fi

echo "==> Database"
php artisan migrate --force
php artisan app:bootstrap --force

echo "==> Frontend"
npm ci
npm run build

echo "==> Health gate"
APP_URL="${APP_URL:-http://localhost}"
if [ -f .env ]; then
  APP_URL="$(grep -E '^APP_URL=' .env | head -1 | cut -d= -f2- | tr -d '"' || echo http://localhost)"
fi

for i in $(seq 1 30); do
  if curl -fsS "${APP_URL}/health" >/dev/null 2>&1; then
    echo "Health check OK: ${APP_URL}/health"
    echo "==> Install complete"
    exit 0
  fi
  sleep 2
done

echo "Warning: /health did not respond. Start the web server and verify manually."
echo "==> Install complete (pending health)"
