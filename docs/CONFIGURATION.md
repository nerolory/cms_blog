# Configuration reference

**Russian:** [docs/ru/CONFIGURATION.md](ru/CONFIGURATION.md)

All environment variables used by the application, Docker Compose, and optional AI service.  
Copy templates: [`.env.example`](../.env.example) (development), [`.env.production.example`](../.env.production.example) (production).

---

## Application

| Variable | Example | Description |
|----------|---------|-------------|
| `APP_NAME` | `CMS Blog` | Application name (UI, mail) |
| `APP_ENV` | `local` / `production` | Environment |
| `APP_KEY` | base64:… | **Required.** `php artisan key:generate` |
| `APP_DEBUG` | `true` / `false` | **Must be `false` in production** |
| `APP_URL` | `https://example.com` | Public base URL (HTTPS in prod) |
| `APP_LOCALE` | `en` / `ru` | Default locale |
| `APP_FALLBACK_LOCALE` | `en` | Fallback locale |
| `APP_AUTO_BOOTSTRAP` | `true` / `false` | Auto migrate+seed on Docker start (dev only) |
| `APP_MAINTENANCE_DRIVER` | `file` / `redis` | Maintenance mode storage |

---

## Docker host ports

Used in `docker-compose.yml` / `docker-compose.prod.yml` — **no hardcoded host ports in compose files**.

| Variable | Default | Maps to |
|----------|---------|---------|
| `HOST_HTTP_PORT` | `80` | nginx → 80 |
| `HOST_POSTGRES_PORT` | `5432` | PostgreSQL |
| `HOST_REDIS_PORT` | `6379` | Redis |
| `HOST_VITE_PORT` | `5173` | Vite dev server (php container) |
| `HOST_AI_SERVICE_PORT` | `8100` | AI FastAPI → 8000 |
| `MAILPIT_SMTP_PORT` | `1025` | Mailpit SMTP |
| `MAILPIT_UI_PORT` | `8025` | Mailpit web UI |

---

## Database

| Variable | Dev (Docker) | Production |
|----------|--------------|------------|
| `DB_CONNECTION` | `pgsql` | `pgsql` |
| `DB_HOST` | `database` | `database` or external host |
| `DB_PORT` | `5432` | `5432` |
| `DB_DATABASE` | `laravel` | unique name |
| `DB_USERNAME` | `laravel` | strong user |
| `DB_PASSWORD` | secret | **strong password** |
| `DB_ALLOW_DESTRUCTIVE` | `false` | **never `true` on prod DB** |

---

## Redis

| Variable | Description |
|----------|-------------|
| `REDIS_HOST` | `redis` in Docker |
| `REDIS_PORT` | `6379` |
| `REDIS_PASSWORD` | **Required in production** |
| `REDIS_CLIENT` | `phpredis` |
| `CACHE_STORE` | `redis` (prod) |
| `SESSION_DRIVER` | `redis` (prod) |
| `QUEUE_CONNECTION` | `redis` (prod) |

---

## Mail

| Variable | Description |
|----------|-------------|
| `MAIL_MAILER` | `mailpit` (dev), `smtp` (prod) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` | SMTP |
| `MAIL_SCHEME` | `tls` for production |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Sender |
| `MAILPIT_HOST` | `mailpit` inside Docker network |
| `REQUIRE_EMAIL_VERIFICATION` | `true` recommended in prod |

---

## Security

| Variable | Description |
|----------|-------------|
| `SECURITY_HSTS_ENABLED` | `true` behind HTTPS |
| `SECURITY_HSTS_MAX_AGE` | e.g. `31536000` |
| `SECURITY_CSP_ENABLED` | Content-Security-Policy |
| `SESSION_ENCRYPT` | `true` in prod |
| `SESSION_SECURE_COOKIE` | `true` behind HTTPS |
| `HEALTH_ALLOWED_IPS` | CSV IP/CIDR for `/health` detail access (prod) |
| `HEALTH_EXPOSE_PROBE_DETAILS` | optional override |

---

## Payments

| Variable | Description |
|----------|-------------|
| `PAYMENT_GATEWAY` | `mock` (local), `http` (prod), `none`/`disabled` |
| `PAYMENT_HTTP_URL` | Base URL external payment API |
| `PAYMENT_HTTP_SECRET` | Bearer token for outbound charges |
| `PAYMENT_HTTP_TIMEOUT` | seconds |
| `PAYMENT_WEBHOOK_SECRET` | HMAC secret for `POST /api/webhooks/payment` |

---

## AI service (Python)

| Variable | Description |
|----------|-------------|
| `AI_SERVICE_URL` | Laravel → AI worker (e.g. `http://ai-service:8000`) |
| `AI_SERVICE_DATABASE_URL` | PostgreSQL URL for AI worker |
| `OPENAI_API_KEY` | Optional LLM provider |
| `OPENAI_BASE_URL` | API base |
| `OPENAI_MODEL` | Model id |

---

## Bootstrap / seeding

| Variable | Description |
|----------|-------------|
| `SEED_OWNER_EMAIL` | Initial owner account |
| `SEED_OWNER_PASSWORD` | **Strong password in prod** (guard rejects `password`) |
| `SEED_OWNER_NAME` | Display name |

---

## Frontend build

| Variable | Description |
|----------|-------------|
| `VITE_APP_NAME` | Passed to Vite |

---

## Search (optional profiles)

Uncomment services in `docker-compose.yml` and set:

| Variable | Purpose |
|----------|---------|
| `SEARCH_ELASTICSEARCH_ENABLED` | Enable ES backend |
| `SEARCH_ELASTICSEARCH_PORT` | Host port when profile enabled |

---

## Related docs

- Install: [`install/INSTALL.md`](install/INSTALL.md)
- Production checklist: [`PRODUCTION_DEPLOY.md`](PRODUCTION_DEPLOY.md)
- Operations: [`OPERATIONS.md`](OPERATIONS.md)
