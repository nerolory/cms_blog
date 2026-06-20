#!/usr/bin/env bash
# До composer install: минимальный .env, чтобы package:discover не считал окружение production.
set -euo pipefail

if [[ ! -f .env ]]; then
  cp .env.example .env
fi

set_env_var() {
  local key="$1"
  local value="$2"
  if grep -q "^${key}=" .env; then
    sed -i "s|^${key}=.*|${key}=${value}|" .env
  else
    echo "${key}=${value}" >> .env
  fi
}

# CI / GitHub Actions: без Redis и PostgreSQL до migrate.
if [[ "${GITHUB_ACTIONS:-}" == "true" ]]; then
  set_env_var APP_ENV local
  set_env_var CACHE_STORE array
  set_env_var SESSION_DRIVER file
  set_env_var QUEUE_CONNECTION sync
fi

mkdir -p database
touch database/database.sqlite
