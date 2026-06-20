#!/usr/bin/env bash
# Локальная симуляция всех 6 jobs из .github/workflows/code-quality.yml
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

export GITHUB_ACTIONS=true
FAILED=0
REDIS_CID=""

cleanup_redis() {
  if [[ -n "$REDIS_CID" ]]; then
    docker stop "$REDIS_CID" >/dev/null 2>&1 || true
  fi
}

ensure_redis() {
  if command -v redis-cli >/dev/null 2>&1 && redis-cli -h 127.0.0.1 ping >/dev/null 2>&1; then
    return 0
  fi

  if ! command -v docker >/dev/null 2>&1; then
    echo "Redis not available on 127.0.0.1:6379 and Docker is missing" >&2
    return 1
  fi

  REDIS_CID=$(docker run -d --name "cms-blog-ci-redis-$$" -p 6379:6379 redis:7-alpine)
  trap cleanup_redis EXIT

  for _ in $(seq 1 15); do
    if docker exec "$REDIS_CID" redis-cli ping >/dev/null 2>&1; then
      return 0
    fi
    sleep 1
  done

  echo "Redis container did not become ready" >&2
  return 1
}

run_step() {
  local name="$1"
  shift
  echo ""
  echo "========== $name =========="
  if "$@"; then
    echo "OK: $name"
  else
    echo "FAIL: $name" >&2
    FAILED=1
  fi
}

run_step "bootstrap (ci-prepare-env + composer install)" bash -c '
  rm -f .env
  bash scripts/ci-prepare-env.sh
  composer install --prefer-dist --no-progress --no-interaction
  php artisan key:generate --force --ansi
'

run_step "php-quality (composer check:quality)" composer check:quality

run_step "npm ci" npm ci

echo ""
echo "========== php-tests (build + artisan test + Redis) =========="
if ensure_redis && npm run build \
  && env APP_ENV=testing REDIS_HOST=127.0.0.1 REDIS_PORT=6379 php artisan test; then
  echo "OK: php-tests (build + artisan test + Redis)"
else
  echo "FAIL: php-tests (build + artisan test + Redis)" >&2
  FAILED=1
fi

run_step "frontend (check:all)" npm run check:all

run_step "php-tests-pgsql (phpunit.integration)" bash -c '
  if ! command -v pg_isready >/dev/null 2>&1; then
    echo "SKIP: PostgreSQL client not available"
    exit 0
  fi
  export APP_ENV=testing PG_INTEGRATION_TESTS=true DB_ALLOW_DESTRUCTIVE=true
  export DB_CONNECTION=pgsql DB_HOST="${DB_HOST:-127.0.0.1}" DB_PORT="${DB_PORT:-5432}"
  export DB_DATABASE="${DB_DATABASE:-laravel_test}" DB_USERNAME="${DB_USERNAME:-laravel}"
  export DB_PASSWORD="${DB_PASSWORD:-secret}"
  vendor/bin/phpunit --configuration phpunit.integration.xml
'

run_step "e2e-smoke" bash -c '
  rm -f database/database.sqlite
  touch database/database.sqlite
  mkdir -p storage/framework/sessions
  env APP_ENV=testing DB_DATABASE=database/database.sqlite SESSION_DRIVER=file \
    php artisan migrate --force
  env APP_ENV=testing DB_DATABASE=database/database.sqlite SESSION_DRIVER=file \
    php artisan db:seed --class=E2eFixturesSeeder --force
  npm run build
  env APP_ENV=testing DB_DATABASE=database/database.sqlite SESSION_DRIVER=file \
    php artisan serve --host=127.0.0.1 --port=8000 &
  SERVER_PID=$!
  trap "kill ${SERVER_PID} 2>/dev/null || true" EXIT
  for i in $(seq 1 30); do
    if curl -sf http://127.0.0.1:8000/up >/dev/null 2>&1; then
      break
    fi
    sleep 1
  done
  env PLAYWRIGHT_BASE_URL=http://127.0.0.1:8000 CI=true npm run test:e2e
'

echo ""
if [[ "$FAILED" -eq 0 ]]; then
  echo "All CI steps passed."
else
  echo "One or more CI steps failed." >&2
  exit 1
fi
