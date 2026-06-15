# CMS Блог

Production-ready **CMS-блог** на **Laravel 13**: CRUD постов с модерацией, RBAC, SEO, API (Sanctum), админка Filament, опциональный Python AI worker и несколько способов развёртывания (native, Docker, Kubernetes).

**English:** [README.md](../../README.md)  
**Лицензия:** [CMS Blog License (CBL) 1.0](../../LICENSE) — коммерческое развёртывание и интеграция **разрешены**, продажа продукта как отдельного товара — **нет**. Кратко: [LICENSE.md](LICENSE.md).

---

## Возможности

| Область | Реализация |
|---------|------------|
| Архитектура | Controller → Service (contract) → Repository, DTO |
| Контент | Посты, версии, preview, комментарии, реакции, полнотекстовый поиск |
| Админка | Filament 4 + Shield, модерация |
| API | Sanctum, OpenAPI (`/docs/api`) |
| SEO | JSON-LD, sitemap, Conditional GET / ETag |
| Платежи | Mock (local), HTTP gateway + webhook (prod) |
| AI | FastAPI sidecar (`docker compose --profile ai`) |
| Качество | PHPStan L9, 200+ PHPUnit, E2E, Vitest + TypeScript |

Дорожная карта: [ROADMAP.md](ROADMAP.md) (закрытая / открытая часть).

---

## Быстрый старт (Docker)

```bash
cp .env.example .env
# Для Docker: DB_CONNECTION=pgsql, DB_HOST=database — см. CONFIGURATION.md
docker compose up -d
docker compose exec php composer setup
```

Сайт: `http://localhost` (порт `HOST_HTTP_PORT`, по умолчанию 80).

---

## Установка (все способы)

| Способ | Документ |
|--------|----------|
| Обзор | [install/INSTALL.md](install/INSTALL.md) |
| Docker (dev + prod) | [install/DOCKER.md](install/DOCKER.md) |
| Linux / macOS (native) | [install/LINUX.md](install/LINUX.md) |
| Windows | [install/WINDOWS.md](install/WINDOWS.md) |
| Kubernetes (Helm) | [install/KUBERNETES.md](install/KUBERNETES.md) |
| Production checklist | [PRODUCTION_DEPLOY.md](../PRODUCTION_DEPLOY.md) (English) |
| **Все переменные окружения** | [CONFIGURATION.md](CONFIGURATION.md) |

Автоустановка:

```bash
make install
bash scripts/install.sh
powershell -ExecutionPolicy Bypass -File scripts/install.ps1
```

---

## Конфигурация

1. Скопируйте [`.env.example`](../../.env.example) или [`.env.production.example`](../../.env.production.example).
2. `php artisan key:generate`
3. Порты хоста задаются **только через `.env`** (`HOST_HTTP_PORT`, `HOST_POSTGRES_PORT`, …).
4. Владелец: `php artisan app:bootstrap` (`SEED_OWNER_*`).

Полный справочник: [CONFIGURATION.md](CONFIGURATION.md).

---

## Проверки качества

```bash
docker compose exec php composer check:php
npm run check:frontend && npm run build
make ci   # или .\scripts\ci.ps1 на Windows
```

Подробнее (English): [QUALITY.md](../QUALITY.md) · [CI.md](../CI.md).

---

## Коммерческое использование

Разрешено: развёртывание у клиентов, интеграция в другие системы, платные и **бесплатные** услуги по установке/настройке.  
Запрещено: продажа репозитория как самостоятельного коммерческого продукта.

Подробнее: [COMMERCIAL_SERVICES.md](COMMERCIAL_SERVICES.md).

---

## Документация

| Файл | Назначение |
|------|------------|
| [ROADMAP.md](ROADMAP.md) | Реализовано / в планах |
| [CONFIGURATION.md](CONFIGURATION.md) | Переменные окружения |
| [COMMERCIAL_SERVICES.md](COMMERCIAL_SERVICES.md) | Услуги интеграции |
| [LICENSE.md](LICENSE.md) | Лицензия (кратко) |
| [CODING_STANDARDS.md](../CODING_STANDARDS.md) | Стандарты кода (English) |
| [ROUTING.md](../ROUTING.md) | Маршруты (English) |

Внутренние файлы (техдолг, планы агента, аудит) **не входят в release-архив** — см. [`.gitattributes`](../../.gitattributes).

---

## Стек

PHP 8.3 · Laravel 13 · PostgreSQL · Redis · Filament · Vite · Bootstrap 5 · FastAPI · Docker · Helm
