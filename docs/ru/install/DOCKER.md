**English:** [docs/install/DOCKER.md](../../install/DOCKER.md)

Полное руководство по развёртыванию через Docker Compose (development и production).  
Конфигурация: [CONFIGURATION.md](../CONFIGURATION.md) · обзор: [INSTALL.md](INSTALL.md).

---

## Prerequisites

- Docker Engine 24+ / Docker Desktop 4.x
- Docker Compose v2 (`docker compose`)
- Git
- Для production build frontend: Node 20+ **на хосте** (или multi-stage image — см. prod profile)

---

## Development stack

### 1. Environment

```bash
cp .env.example .env
```

Минимум для Docker (раскомментируйте или установите):

```dotenv
DB_CONNECTION=pgsql
DB_HOST=database
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PORT=6379

HOST_HTTP_PORT=80
HOST_POSTGRES_PORT=5432
HOST_REDIS_PORT=6379
HOST_VITE_PORT=5173
```

Все host-порты задаются **только в `.env`** — в `docker-compose.yml` нет «сырых» значений.

### 2. Start services

```bash
docker compose up -d --build
docker compose exec php composer setup
```

Сервисы: `php`, `nginx`, `database`, `redis`, `mailpit`.

### 3. Vite (frontend dev)

На **хосте** (не в Linux-контейнере при Windows mount):

```bash
npm install
npm run dev
```

Vite слушает порт `HOST_VITE_PORT` (проброшен из контейнера `php`).

### 4. Verify

```bash
curl -fsS "http://localhost:${HOST_HTTP_PORT:-80}/health"
open "http://localhost:${HOST_HTTP_PORT:-8025}"   # Mailpit UI (MAILPIT_UI_PORT)
```

---

## Optional: queue worker (dev)

```bash
docker compose --profile prod up -d queue
```

В `.env`: `QUEUE_CONNECTION=redis`.

---

## Optional: AI service

```bash
# В .env:
AI_SERVICE_URL=http://ai-service:8000
HOST_AI_SERVICE_PORT=8100
AI_SERVICE_DATABASE_URL=postgresql://${DB_USERNAME}:${DB_PASSWORD}@database:5432/${DB_DATABASE}
OPENAI_API_KEY=sk-...   # optional

docker compose --profile ai up -d ai-service
curl -fsS "http://localhost:${HOST_AI_SERVICE_PORT:-8100}/health"
```

Laravel обращается к worker по **внутреннему** URL `AI_SERVICE_URL`; с хоста проверяйте `HOST_AI_SERVICE_PORT`.

---

## Production stack

Файл: [`docker-compose.prod.yml`](../../../docker-compose.prod.yml)

### 1. Environment

```bash
cp .env.production.example .env
```

Обязательно:

| Variable | Production value |
|----------|------------------|
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |
| `APP_URL` | `https://your-domain` |
| `DB_*` | strong credentials |
| `REDIS_PASSWORD` | set |
| `SESSION_SECURE_COOKIE` | `true` (HTTPS) |
| `PAYMENT_GATEWAY` | `http` or `none` |
| `SEED_OWNER_PASSWORD` | strong (not `password`) |

Host ports (same as dev): `HOST_HTTP_PORT`, etc.

### 2. One-shot install profile

Собирает prod image, выполняет migrate + bootstrap:

```bash
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml --profile install up install
```

Затем соберите frontend **на хосте** (если не в CI):

```bash
npm ci && npm run build
```

### 3. Long-running production

```bash
docker compose -f docker-compose.prod.yml --profile prod up -d
```

Включает: `php`, `nginx`, `database`, `redis`, `queue`.  
Storage и bootstrap cache — named volumes (`laravel_storage`, `laravel_bootstrap_cache`).

### 4. AI in production

```bash
docker compose -f docker-compose.prod.yml --profile prod --profile ai up -d
```

---

## Service map

```mermaid
flowchart TB
  browser[Browser] --> nginx[nginx :HOST_HTTP_PORT]
  nginx --> php[php Laravel]
  php --> db[(PostgreSQL)]
  php --> redis[(Redis)]
  php --> mailpit[mailpit optional]
  php --> ai[ai-service profile ai]
  queue[queue profile prod] --> redis
  queue --> db
  ai --> db
```

---

## Health checks

| Endpoint | Container | Purpose |
|----------|-----------|---------|
| `GET /up` | nginx → php | Liveness |
| `GET /health` | nginx → php | Readiness (DB, Redis, …) |
| `GET /health` | ai-service:8000 | AI worker |

Production: ограничьте детали `/health` через `HEALTH_ALLOWED_IPS` — см. CONFIGURATION.

---

## Common operations

```bash
# Logs
docker compose logs -f php nginx

# Artisan
docker compose exec php php artisan migrate --force
docker compose exec php php artisan config:cache
docker compose exec php php artisan route:cache

# Shell
docker compose exec php bash

# Rebuild after Dockerfile change
docker compose up -d --build
```

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Port already in use | Change `HOST_HTTP_PORT` / `HOST_POSTGRES_PORT` in `.env` |
| `SQLSTATE connection refused` | `DB_HOST=database`, wait for `database` healthy |
| Slow Blade on Windows | Named volumes for `storage/framework/views` already configured |
| npm in container fails on Windows | Run `npm` on host — see Makefile `ci-frontend` |
| AI service unhealthy | Set `AI_SERVICE_DATABASE_URL`; check Postgres credentials |
| Permission denied `storage/` | `docker compose exec php chown -R www-data:www-data storage bootstrap/cache` |

---

## CI parity

```bash
make ci              # Linux/macOS
.\scripts\ci.ps1     # Windows
```

---

## Related

- Native install: [LINUX.md](LINUX.md), [WINDOWS.md](WINDOWS.md)
- Kubernetes: [KUBERNETES.md](KUBERNETES.md)
- Production checklist: [PRODUCTION_DEPLOY.md](../../PRODUCTION_DEPLOY.md)
