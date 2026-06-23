#!/usr/bin/env bash
# Симуляция GitHub Actions: .env до composer install, затем package:discover.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

backup=""
if [[ -f .env ]]; then
  backup="$(mktemp)"
  cp .env "$backup"
fi

cleanup() {
  if [[ -n "$backup" && -f "$backup" ]]; then
    mv -f "$backup" .env
  else
    rm -f .env
  fi
}
trap cleanup EXIT

rm -f .env
export GITHUB_ACTIONS=true
bash scripts/ci-prepare-env.sh

if [[ ! -f vendor/autoload.php ]]; then
  composer install --prefer-dist --no-progress --no-interaction
else
  php artisan package:discover --ansi
  php artisan filament:upgrade
fi

echo "CI bootstrap OK"
