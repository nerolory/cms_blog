# CMS Blog

A production-oriented **Laravel 13** CMS blog: post CRUD with moderation, RBAC, SEO, API (Sanctum), Filament admin, optional Python AI worker, and multi-channel deployment (native, Docker, Kubernetes).

**Russian documentation:** [docs/ru/README.md](docs/ru/README.md)  
**License:** [CMS Blog License (CBL) 1.0](LICENSE) — commercial deployment and integration allowed; **standalone product resale is not**.

---

## Features

| Area | Implementation |
|------|----------------|
| Architecture | Controller → Service (contract) → Repository, DTOs, domain events |
| Content | Posts, versions, preview, comments, reactions, full-text search |
| Admin | Filament 4 + Shield, moderation pipeline |
| API | Sanctum, OpenAPI via Scramble (`/docs/api`) |
| SEO & cache | JSON-LD, sitemap, Conditional GET / ETag |
| Payments | Mock (local), HTTP gateway + webhook (production) |
| AI | Optional FastAPI sidecar (`docker compose --profile ai`) |
| Quality | PHPStan L9, 200+ PHPUnit, Playwright E2E, Vitest + TypeScript |
| Security | CSP nonce, rate limits, production config guards, health IP allowlist |

Full roadmap (done vs planned): [`docs/ROADMAP.md`](docs/ROADMAP.md).

---

## Quick start (Docker — recommended)

```bash
cp .env.example .env
# For Docker: set DB_CONNECTION=pgsql, DB_HOST=database (see docs/CONFIGURATION.md)
docker compose up -d
docker compose exec php composer setup
```

Open `http://localhost` (port from `HOST_HTTP_PORT`, default **80**).  
Posts: `/posts` · Health: `/health` · API docs: `/docs/api`

---

## Installation guides

| Method | Document |
|--------|----------|
| **Overview & post-install checks** | [`docs/install/INSTALL.md`](docs/install/INSTALL.md) |
| **Docker** (dev + prod compose) | [`docs/install/DOCKER.md`](docs/install/DOCKER.md) |
| **Linux / macOS** (native PHP) | [`docs/install/LINUX.md`](docs/install/LINUX.md) |
| **Windows** (native + Docker) | [`docs/install/WINDOWS.md`](docs/install/WINDOWS.md) |
| **Kubernetes** (Helm chart) | [`docs/install/KUBERNETES.md`](docs/install/KUBERNETES.md) |
| **Production checklist** | [`docs/PRODUCTION_DEPLOY.md`](docs/PRODUCTION_DEPLOY.md) |
| **All environment variables** | [`docs/CONFIGURATION.md`](docs/CONFIGURATION.md) |

Russian install guides: [`docs/ru/install/INSTALL.md`](docs/ru/install/INSTALL.md) · [`docs/ru/install/DOCKER.md`](docs/ru/install/DOCKER.md) · [`docs/ru/install/LINUX.md`](docs/ru/install/LINUX.md) · [`docs/ru/install/WINDOWS.md`](docs/ru/install/WINDOWS.md) · [`docs/ru/install/KUBERNETES.md`](docs/ru/install/KUBERNETES.md)

One-liner installers:

```bash
make install          # Linux/macOS or Windows (Makefile detects OS)
bash scripts/install.sh
powershell -ExecutionPolicy Bypass -File scripts/install.ps1
```

---

## Configuration

1. Copy [`.env.example`](.env.example) (development) or [`.env.production.example`](.env.production.example) (production).
2. Generate key: `php artisan key:generate`
3. Set **all host ports** via env (`HOST_HTTP_PORT`, `HOST_POSTGRES_PORT`, …) — Docker Compose does not hardcode them.
4. Bootstrap owner: `php artisan app:bootstrap` (uses `SEED_OWNER_*`).

Complete variable reference: [`docs/CONFIGURATION.md`](docs/CONFIGURATION.md) · Russian: [`docs/ru/CONFIGURATION.md`](docs/ru/CONFIGURATION.md).

---

## Development & quality gates

```bash
# PHP (Docker)
docker compose exec php composer check:php

# Frontend (host — do not run npm inside Linux container on Windows mounts)
npm install && npm run check:frontend && npm run build

# Full local CI
make ci                    # Linux/macOS
.\scripts\ci.ps1           # Windows
```

Details: [`docs/QUALITY.md`](docs/QUALITY.md) · [`docs/CI.md`](docs/CI.md).

---

## Documentation index

| Document | Purpose |
|----------|---------|
| [`docs/ROADMAP.md`](docs/ROADMAP.md) | Implemented vs planned |
| [`docs/ru/ROADMAP.md`](docs/ru/ROADMAP.md) | Roadmap (Russian) |
| [`docs/CODING_STANDARDS.md`](docs/CODING_STANDARDS.md) | Architecture & code style |
| [`docs/ROUTING.md`](docs/ROUTING.md) | URL / controller conventions |
| [`docs/OPERATIONS.md`](docs/OPERATIONS.md) | Runtime operations |
| [`docs/adr/INDEX.md`](docs/adr/INDEX.md) | Architecture Decision Records |
| [`docs/COMMERCIAL_SERVICES.md`](docs/COMMERCIAL_SERVICES.md) | Integration & deployment services |
| [`docs/ru/README.md`](docs/ru/README.md) | Full documentation hub (Russian) |

Internal agent/remediation documents are **excluded from release archives** (see [`.gitattributes`](.gitattributes)).

---

## Commercial use

You may deploy and integrate this project commercially (client projects, internal tools, free or paid setup services). You may **not** resell the codebase as a standalone commercial product.

The maintainer may also offer professional deployment and integration — see [`docs/COMMERCIAL_SERVICES.md`](docs/COMMERCIAL_SERVICES.md) · Russian: [`docs/ru/COMMERCIAL_SERVICES.md`](docs/ru/COMMERCIAL_SERVICES.md).

---

## Stack

- **Backend:** PHP 8.3, Laravel 13, PostgreSQL, Redis, Filament 4  
- **Frontend:** Vite, Bootstrap 5, Blade, TypeScript (lib modules)  
- **AI worker:** Python 3.11+, FastAPI  
- **Ops:** Docker Compose, Helm skeleton, GitHub Actions  

---

## Contributing

1. Read [`docs/CODING_STANDARDS.md`](docs/CODING_STANDARDS.md) and [`docs/ROUTING.md`](docs/ROUTING.md).  
2. Run `composer check:php` and `npm run check:frontend` before opening a PR.  
3. Respect the [LICENSE](LICENSE) restrictions on redistribution.

---

## Support

- **Issues:** GitHub issue tracker (bugs, features).  
- **Professional deployment / integration:** [`docs/COMMERCIAL_SERVICES.md`](docs/COMMERCIAL_SERVICES.md).
