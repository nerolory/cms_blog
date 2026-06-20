# CI и branch protection

## GitHub Actions

Файл: [`.github/workflows/code-quality.yml`](../.github/workflows/code-quality.yml)

| Job | Что проверяет | Зависимости на runner |
|-----|----------------|------------------------|
| `php-quality` | `composer check:quality` — Pint, line-length, PHPDoc, PHPStan 9, composer audit | PHP 8.3 |
| `python-quality` | `ruff` + `pytest` в `ai-service/` | Python 3.12 |
| `php-tests` | PHPUnit (`APP_ENV=testing`, SQLite in-memory) | PHP 8.3 + **ext-redis**, **Redis service** (`127.0.0.1:6379`), **Node 20**, `npm ci && npm run build` (manifest для `@vite`) |
| `php-tests-pgsql` | PostgreSQL integration — tsvector/GIN (`phpunit.integration.xml`) | PHP 8.3 + **Postgres service** (`127.0.0.1:5432`) |
| `frontend-quality` | ESLint, TypeScript, Vitest+coverage, Stylelint, W3C HTML, blade-formatter, Vite build, npm audit | Node 20 |
| `e2e-smoke` | Playwright (smoke + form dirty-state) против `artisan serve` | PHP + Node, SQLite file, `npm run build`, **`SESSION_DRIVER=file`** (не `array` из `.env.testing`) |

Триггеры: push и pull request в ветки `main`, `develop`.

## Локальный прогон (эквивалент CI)

```bash
# Полный прогон (frontend build перед PHP — иначе Vite manifest отсутствует в тестах)
make ci
# или scripts/ci.sh / scripts/ci.ps1

# PHP unit (эквивалент job php-tests)
npm ci && npm run build
# Redis на 127.0.0.1:6379 (docker compose up redis -d или scripts/verify-github-ci.sh поднимет контейнер)
REDIS_HOST=127.0.0.1 REDIS_PORT=6379 APP_ENV=testing php artisan test

# Python
cd ai-service && pip install -e ".[dev]" && ruff check . && pytest

# PostgreSQL integration (нужен PG с БД laravel_test)
php artisan test --configuration=phpunit.integration.xml

# Frontend (на хосте)
npm run check:frontend && npm run build

# E2E (после migrate + E2eFixturesSeeder; сессия между запросами — file, не array)
mkdir -p storage/framework/sessions
APP_ENV=testing DB_DATABASE=database/database.sqlite SESSION_DRIVER=file php artisan migrate --force
APP_ENV=testing DB_DATABASE=database/database.sqlite SESSION_DRIVER=file \
  php artisan db:seed --class=E2eFixturesSeeder --force
npm run build
APP_ENV=testing DB_DATABASE=database/database.sqlite SESSION_DRIVER=file \
  php artisan serve --host=127.0.0.1 --port=8000 &
npm run test:e2e
```

Windows: [`scripts/ci.ps1`](../scripts/ci.ps1)  
Linux/macOS: [`scripts/ci.sh`](../scripts/ci.sh) или `make ci`  
Полная симуляция всех 6 jobs GitHub Actions: [`scripts/verify-github-ci.sh`](../scripts/verify-github-ci.sh)

### Почему frontend не в Docker

Каталог проекта смонтирован в контейнер. `node_modules`, установленные на Windows, содержат бинарники под win32; в Linux-контейнере `vite build` падает. GitHub Actions ставит зависимости на ubuntu-latest — там всё работает через `npm ci`.

## Branch protection (USER ACTION)

После push в GitHub настройте вручную — см. [`PRODUCTION_DEPLOY.md`](PRODUCTION_DEPLOY.md).

### Через веб-интерface

1. **Settings → Branches → Add branch protection rule**
2. Branch name pattern: `main`
3. Включить:
   - **Require a pull request before merging**
   - **Require status checks to pass before merging**
   - Status checks: `php-quality`, `python-quality`, `php-tests`, `php-tests-pgsql`, `frontend-quality`, `e2e-smoke`
   - **Require branches to be up to date before merging**
4. Сохранить правило

### Через GitHub CLI

```bash
gh api repos/{owner}/{repo}/branches/main/protection \
  --method PUT \
  --field required_status_checks[strict]=true \
  --field required_status_checks[contexts][]=php-quality \
  --field required_status_checks[contexts][]=python-quality \
  --field required_status_checks[contexts][]=php-tests \
  --field required_status_checks[contexts][]=php-tests-pgsql \
  --field required_status_checks[contexts][]=frontend-quality \
  --field required_status_checks[contexts][]=e2e-smoke \
  --field enforce_admins=true \
  --field required_pull_request_reviews[required_approving_review_count]=1 \
  --field restrictions=null
```

## Pre-commit hook (опционально)

```bash
git config core.hooksPath .githooks
chmod +x .githooks/pre-commit   # Linux/macOS
```

Hook запускает `composer check:php` в Docker и `npm run check:frontend` на хосте.

## Критерии готовности

- [x] `composer check:php` — зелёный
- [x] `npm run check:frontend && npm run build` — зелёный
- [x] Python quality в CI
- [x] Vitest + TypeScript в CI
- [x] Workflow описан и готов к push
- [x] PG integration job добавлен
- [x] E2E dirty-state в CI
- [ ] Branch protection на `main` — **USER ACTION** — [`PRODUCTION_DEPLOY.md`](PRODUCTION_DEPLOY.md)
