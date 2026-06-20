#!/usr/bin/env bash
# Полный прогон проверок (Linux/macOS).
# PHP — в Docker, Python и frontend — на хосте.

set -euo pipefail
cd "$(dirname "$0")/.."

echo "==> Frontend (host)"
npm run check:frontend
npm run build

echo "==> PHP (Docker)"
docker compose exec -T php composer check:php

echo "==> Python (ai-service)"
(
  cd ai-service
  pip install -e ".[dev]" -q
  ruff check .
  pytest
)

echo "==> CI passed"
