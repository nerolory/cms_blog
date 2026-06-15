# Обзор установки

**English:** [docs/install/INSTALL.md](../../install/INSTALL.md)

Единая точка входа для развёртывания **CMS Blog** на хосте, в Docker или Kubernetes.

**English readme:** [README.md](../../../README.md) · **Русская документация:** [README.md](../README.md) · **Конфигурация:** [CONFIGURATION.md](../CONFIGURATION.md)

---

## Выбор способа установки

| Сценарий | Рекомендуемый путь | Документ |
|----------|-------------------|----------|
| Локальная разработка (любая ОС) | Docker Compose | [DOCKER.md](DOCKER.md) |
| Production на VPS / bare metal | Docker prod compose или native + systemd | [DOCKER.md](DOCKER.md), [LINUX.md](LINUX.md) |
| Windows без WSL | Docker Desktop или Laragon | [WINDOWS.md](WINDOWS.md) |
| Оркестрация / масштабирование | Helm (skeleton) | [KUBERNETES.md](KUBERNETES.md) |
| Production checklist | — | [PRODUCTION_DEPLOY.md](../../PRODUCTION_DEPLOY.md) (English) |

---

## Быстрый старт (одна команда)

| Канал | Команда |
|-------|---------|
| Make (auto OS) | `make install` |
| Linux / macOS | `bash scripts/install.sh` |
| Windows | `powershell -ExecutionPolicy Bypass -File scripts/install.ps1` |
| Docker dev | `cp .env.example .env && docker compose up -d && docker compose exec php composer setup` |
| Docker prod (one-shot) | `cp .env.production.example .env && docker compose -f docker-compose.prod.yml --profile install up install` |
| Docker prod (runtime) | `docker compose -f docker-compose.prod.yml --profile prod up -d` |

---

## Обязательная конфигурация (все способы)

1. Скопируйте шаблон окружения:
   - разработка: [`.env.example`](../../../.env.example)
   - production: [`.env.production.example`](../../../.env.production.example)
2. Сгенерируйте ключ: `php artisan key:generate`
3. Настройте БД (`DB_*`), Redis (`REDIS_*`), почту (`MAIL_*`).
4. **Docker:** все порты хоста — только через `.env` (`HOST_HTTP_PORT`, `HOST_POSTGRES_PORT`, …). См. [CONFIGURATION.md](../CONFIGURATION.md).
5. Создайте владельца: `php artisan app:bootstrap` (переменные `SEED_OWNER_*`).
6. Production: `APP_DEBUG=false`, сильные пароли, `PAYMENT_GATEWAY=http`, HTTPS, см. [PRODUCTION_DEPLOY.md](../../PRODUCTION_DEPLOY.md).

---

## Post-install verification

```bash
curl -fsS "http://localhost:${HOST_HTTP_PORT:-80}/health"
curl -fsS "http://localhost:${HOST_HTTP_PORT:-80}/up"
```

Ожидается HTTP **200** и JSON со статусом probes.

Дополнительно:

```bash
docker compose exec php php artisan about
docker compose exec php composer check:php
npm run build
```

| URL | Назначение |
|-----|------------|
| `/` | Главная |
| `/posts` | Список постов |
| `/admin` | Filament (owner) |
| `/docs/api` | OpenAPI (Scramble) |
| `/health` | Readiness |
| `/up` | Liveness |

---

## Документация по каналам

- [DOCKER.md](DOCKER.md) — dev и production Compose, AI profile, Mailpit
- [LINUX.md](LINUX.md) — нативная установка Linux/macOS (PHP-FPM, Nginx, systemd)
- [WINDOWS.md](WINDOWS.md) — native PHP, Laragon, Docker Desktop, WSL2
- [KUBERNETES.md](KUBERNETES.md) — Helm chart, Secrets, probes, масштабирование

---

## Опциональные профили Docker

| Profile | Команда | Назначение |
|---------|---------|------------|
| `ai` | `docker compose --profile ai up -d ai-service` | Python FastAPI worker |
| `prod` | `docker compose --profile prod up -d queue` | Queue worker в dev stack |
| `install` | см. DOCKER.md (prod compose) | One-shot migrate + bootstrap |

Переменные AI: `AI_SERVICE_URL`, `AI_SERVICE_DATABASE_URL`, `HOST_AI_SERVICE_PORT`, `OPENAI_*` — [CONFIGURATION.md](../CONFIGURATION.md).

---

## Owner bootstrap

Скрипты установки вызывают `php artisan app:bootstrap --force`, который создаёт owner из:

```
SEED_OWNER_EMAIL=
SEED_OWNER_PASSWORD=
SEED_OWNER_NAME=
```

В production пароль `password` отклоняется guard'ом конфигурации.

---

## Лицензия и коммерческое использование

Развёртывание и интеграция в коммерческих проектах **разрешены**; продажа продукта как отдельного товара — **нет**.  
Подробнее: [LICENSE](../../../LICENSE) · [LICENSE.md](../LICENSE.md) · [COMMERCIAL_SERVICES.md](../COMMERCIAL_SERVICES.md).
