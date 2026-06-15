# Roadmap

**English:** [docs/ROADMAP.md](../ROADMAP.md)

Дорожная карта разделена на **закрытую** (реализовано) и **открытую** (запланировано).  
Статус качества кода: [`QUALITY.md`](../QUALITY.md) · деплой: [`PRODUCTION_DEPLOY.md`](../PRODUCTION_DEPLOY.md).

---

## Закрытая часть (реализовано)

### Ядро приложения

- Laravel 13, PHP 8.3+, архитектура **Controller → Service (contract) → Repository**
- CRUD постов (web + API Sanctum), модерация, версии, preview по signed URL
- RBAC (Spatie + Filament Shield), политики, видимость постов
- SEO: meta, JSON-LD, sitemap, robots.txt, conditional GET / ETag
- Комментарии, реакции, поиск (PostgreSQL FTS + fallback)
- Токены, mock/HTTP payment gateway, webhook подтверждения
- AI: заказы анализа, Python FastAPI worker (profile `ai`)
- Админка Filament 4, health probes, maintenance mode

### Качество и безопасность

- PHPStan L9 (0 errors), Pint, PHPDoc gates, line-length 120
- PHPUnit (200+), Playwright E2E, Vitest + TypeScript для frontend lib
- IDOR/XSS/CSRF/throttle, CSP nonce, production config guards
- W3C Nu Validator — 0 errors на публичных страницах

### Инфраструктура

- Docker Compose (dev + prod profiles), Helm chart skeleton
- CI: PHP, Python, frontend, PG integration, E2E
- Install scripts: `make install`, `scripts/install.sh`, `scripts/install.ps1`
- Документация установки: native Linux/Windows, Docker, Kubernetes

---

## Открытая часть (запланировано)

### Продукт

- [ ] Полноценный Stripe/YooKassa adapter (сейчас: HTTP gateway + webhook contract)
- [ ] Elasticsearch / Solr / Sphinx search profiles (закомментированы в compose)
- [ ] Расширенный AI: streaming, несколько LLM-провайдеров в UI
- [ ] Мультитenant / multi-site (сейчас: single-tenant)

### Frontend

- [ ] Полная миграция entrypoints на TypeScript
- [ ] E2E: avatar crop, TinyMCE dirty-state, admin smoke
- [ ] Storybook / design system поверх Bootstrap 5

### Kubernetes / enterprise

- [ ] Production-ready Helm: Secrets, init Job, PVC, HPA, cert-manager
- [ ] Observability stack (Prometheus metrics, centralized logs)
- [ ] GitOps deploy manifests (Argo CD / Flux) — вне scope MVP

### Документация и релиз

- [ ] Видео walkthrough для portfolio
- [ ] Published Docker images на registry

---

## Вне scope репозитория (оператор)

- GitHub branch protection, production secrets, TLS, off-site backup — [`PRODUCTION_DEPLOY.md`](../PRODUCTION_DEPLOY.md)
