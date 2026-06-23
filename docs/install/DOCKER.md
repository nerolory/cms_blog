# Docker install

**Russian:** [docs/ru/install/DOCKER.md](../ru/install/DOCKER.md)

Full guide for Docker Compose deployment (development and production).  
Configuration: [`../CONFIGURATION.md`](../CONFIGURATION.md) · overview: [`INSTALL.md`](INSTALL.md).

---

## Prerequisites

- Docker Engine 24+ / Docker Desktop 4.x
- Docker Compose v2 (`docker compose`)
- Git
- For production frontend build: Node 20+ **on the host** (or multi-stage image — see prod profile)

---

## Development stack

### 1. Environment

```bash
cp .env.example .env
```

Minimum for Docker (uncomment or set):

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

All host ports are set **only in `.env`** — no hardcoded values in `docker-compose.yml`.

### 2. Start services

```bash
docker compose up -d --build
docker compose exec php composer setup
```

Services: `php`, `nginx`, `database`, `redis`, `mailpit`.

### 3. Vite (frontend dev)

On the **host** (not in the Linux container on Windows bind mounts):

```bash
npm install
npm run dev
```

Vite listens on `HOST_VITE_PORT` (forwarded from the `php` container).

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

In `.env`: `QUEUE_CONNECTION=redis`.

---

## Optional: AI service

```bash
# In .env:
AI_SERVICE_URL=http://ai-service:8000
HOST_AI_SERVICE_PORT=8100
AI_SERVICE_DATABASE_URL=postgresql://${DB_USERNAME}:${DB_PASSWORD}@database:5432/${DB_DATABASE}
OPENAI_API_KEY=sk-...   # optional

docker compose --profile ai up -d ai-service
curl -fsS "http://localhost:${HOST_AI_SERVICE_PORT:-8100}/health"
```

Laravel calls the worker via **internal** URL `AI_SERVICE_URL`; from the host use `HOST_AI_SERVICE_PORT`.

---

## Production stack

File: [`docker-compose.prod.yml`](../../docker-compose.prod.yml)

### 1. Environment

```bash
cp .env.production.example .env
```

Required:

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

Builds prod image, runs migrate + bootstrap:

```bash
docker compose -f docker-compose.prod.yml build
docker compose -f docker-compose.prod.yml --profile install up install
```

Then build frontend **on the host** (if not in CI):

```bash
npm ci && npm run build
```

### 3. Long-running production

```bash
docker compose -f docker-compose.prod.yml --profile prod up -d
```

Includes: `php`, `nginx`, `database`, `redis`, `queue`.  
Storage and bootstrap cache use named volumes (`laravel_storage`, `laravel_bootstrap_cache`).

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

Production: restrict `/health` details via `HEALTH_ALLOWED_IPS` — see CONFIGURATION.

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
| PHP/Blade changes not visible in browser | `docker compose exec php php artisan optimize:clear` then **`docker compose restart php`** (OPcache `validate_timestamps=0` on bind mounts) |
| `502 Bad Gateway` on all pages | PHP-FPM not running or nginx stale upstream — `docker compose ps php`; `docker compose logs php --tail 30`; `docker compose up -d` |
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
- Production checklist: [PRODUCTION_DEPLOY.md](../PRODUCTION_DEPLOY.md)
