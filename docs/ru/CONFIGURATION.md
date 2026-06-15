# Справочник конфигурации

**English:** [docs/CONFIGURATION.md](../CONFIGURATION.md)

Все переменные окружения приложения, Docker Compose и опционального AI-сервиса.  
Шаблоны: [`.env.example`](../../.env.example) (разработка), [`.env.production.example`](../../.env.production.example) (production).

---

## Приложение

| Переменная | Пример | Описание |
|----------|---------|-------------|
| `APP_NAME` | `CMS Blog` | Имя приложения (UI, почта) |
| `APP_ENV` | `local` / `production` | Окружение |
| `APP_KEY` | base64:… | **Обязательно.** `php artisan key:generate` |
| `APP_DEBUG` | `true` / `false` | **В production — `false`** |
| `APP_URL` | `https://example.com` | Публичный URL (HTTPS в prod) |
| `APP_LOCALE` | `en` / `ru` | Локаль по умолчанию |
| `APP_FALLBACK_LOCALE` | `en` | Fallback-локаль |
| `APP_AUTO_BOOTSTRAP` | `true` / `false` | Auto migrate+seed при старте Docker (только dev) |
| `APP_MAINTENANCE_DRIVER` | `file` / `redis` | Хранилище maintenance mode |

---

## Порты хоста Docker

Используются в `docker-compose.yml` / `docker-compose.prod.yml` — **без захардкоженных портов в compose**.

| Переменная | По умолчанию | Проброс |
|----------|---------|---------|
| `HOST_HTTP_PORT` | `80` | nginx → 80 |
| `HOST_POSTGRES_PORT` | `5432` | PostgreSQL |
| `HOST_REDIS_PORT` | `6379` | Redis |
| `HOST_VITE_PORT` | `5173` | Vite dev (контейнер php) |
| `HOST_AI_SERVICE_PORT` | `8100` | AI FastAPI → 8000 |
| `MAILPIT_SMTP_PORT` | `1025` | Mailpit SMTP |
| `MAILPIT_UI_PORT` | `8025` | Mailpit web UI |

---

## База данных

| Переменная | Dev (Docker) | Production |
|----------|--------------|------------|
| `DB_CONNECTION` | `pgsql` | `pgsql` |
| `DB_HOST` | `database` | `database` или внешний хост |
| `DB_PORT` | `5432` | `5432` |
| `DB_DATABASE` | `laravel` | уникальное имя |
| `DB_USERNAME` | `laravel` | надёжный пользователь |
| `DB_PASSWORD` | secret | **сильный пароль** |
| `DB_ALLOW_DESTRUCTIVE` | `false` | **никогда `true` на prod БД** |

---

## Redis

| Переменная | Описание |
|----------|-------------|
| `REDIS_HOST` | `redis` в Docker |
| `REDIS_PORT` | `6379` |
| `REDIS_PASSWORD` | **Обязателен в production** |
| `REDIS_CLIENT` | `phpredis` |
| `CACHE_STORE` | `redis` (prod) |
| `SESSION_DRIVER` | `redis` (prod) |
| `QUEUE_CONNECTION` | `redis` (prod) |

---

## Почта

| Переменная | Описание |
|----------|-------------|
| `MAIL_MAILER` | `mailpit` (dev), `smtp` (prod) |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` | SMTP |
| `MAIL_SCHEME` | `tls` для production |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Отправитель |
| `MAILPIT_HOST` | `mailpit` внутри Docker-сети |
| `REQUIRE_EMAIL_VERIFICATION` | `true` рекомендуется в prod |

---

## Безопасность

| Переменная | Описание |
|----------|-------------|
| `SECURITY_HSTS_ENABLED` | `true` за HTTPS |
| `SECURITY_HSTS_MAX_AGE` | напр. `31536000` |
| `SECURITY_CSP_ENABLED` | Content-Security-Policy |
| `SESSION_ENCRYPT` | `true` в prod |
| `SESSION_SECURE_COOKIE` | `true` за HTTPS |
| `HEALTH_ALLOWED_IPS` | CSV IP/CIDR для деталей `/health` (prod) |
| `HEALTH_EXPOSE_PROBE_DETAILS` | опциональный override |

---

## Платежи

| Переменная | Описание |
|----------|-------------|
| `PAYMENT_GATEWAY` | `mock` (local), `http` (prod), `none`/`disabled` |
| `PAYMENT_HTTP_URL` | Base URL внешнего payment API |
| `PAYMENT_HTTP_SECRET` | Bearer token для исходящих списаний |
| `PAYMENT_HTTP_TIMEOUT` | секунды |
| `PAYMENT_WEBHOOK_SECRET` | HMAC secret для `POST /api/webhooks/payment` |

---

## AI-сервис (Python)

| Переменная | Описание |
|----------|-------------|
| `AI_SERVICE_URL` | Laravel → AI worker (напр. `http://ai-service:8000`) |
| `AI_SERVICE_DATABASE_URL` | PostgreSQL URL для AI worker |
| `OPENAI_API_KEY` | Опциональный LLM-провайдер |
| `OPENAI_BASE_URL` | API base |
| `OPENAI_MODEL` | ID модели |

---

## Bootstrap / seeding

| Переменная | Описание |
|----------|-------------|
| `SEED_OWNER_EMAIL` | Начальный owner |
| `SEED_OWNER_PASSWORD` | **Сильный пароль в prod** (guard отклоняет `password`) |
| `SEED_OWNER_NAME` | Отображаемое имя |

---

## Frontend build

| Переменная | Описание |
|----------|-------------|
| `VITE_APP_NAME` | Передаётся в Vite |

---

## Поиск (опциональные profiles)

Раскомментируйте сервисы в `docker-compose.yml` и задайте:

| Переменная | Назначение |
|----------|---------|
| `SEARCH_ELASTICSEARCH_ENABLED` | Включить ES backend |
| `SEARCH_ELASTICSEARCH_PORT` | Host port при включённом profile |

---

## Связанные документы

- Установка: [install/INSTALL.md](install/INSTALL.md)
- Production checklist: [PRODUCTION_DEPLOY.md](../PRODUCTION_DEPLOY.md) (English)
- Операции: [OPERATIONS.md](../OPERATIONS.md) (English)
