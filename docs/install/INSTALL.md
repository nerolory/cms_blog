# Installation overview

**Russian:** [docs/ru/install/INSTALL.md](../ru/install/INSTALL.md)

Single entry point for deploying **CMS Blog** on a host, in Docker, or on Kubernetes.

Configuration: [`../CONFIGURATION.md`](../CONFIGURATION.md) · root readme: [`../../README.md`](../../README.md)

---

## Choose a deployment path

| Scenario | Recommended path | Document |
|----------|------------------|----------|
| Local development (any OS) | Docker Compose | [DOCKER.md](DOCKER.md) |
| Production on VPS / bare metal | Docker prod compose or native + systemd | [DOCKER.md](DOCKER.md), [LINUX.md](LINUX.md) |
| Windows without WSL | Docker Desktop or Laragon | [WINDOWS.md](WINDOWS.md) |
| Orchestration / scaling | Helm (skeleton) | [KUBERNETES.md](KUBERNETES.md) |
| Production checklist | — | [PRODUCTION_DEPLOY.md](../PRODUCTION_DEPLOY.md) |

---

## One-command quick start

| Channel | Command |
|---------|---------|
| Make (auto OS) | `make install` |
| Linux / macOS | `bash scripts/install.sh` |
| Windows | `powershell -ExecutionPolicy Bypass -File scripts/install.ps1` |
| Docker dev | `cp .env.example .env && docker compose up -d && docker compose exec php composer setup` |
| Docker prod (one-shot) | `cp .env.production.example .env && docker compose -f docker-compose.prod.yml --profile install up install` |
| Docker prod (runtime) | `docker compose -f docker-compose.prod.yml --profile prod up -d` |

---

## Required configuration (all methods)

1. Copy an environment template:
   - development: [`.env.example`](../../.env.example)
   - production: [`.env.production.example`](../../.env.production.example)
2. Generate key: `php artisan key:generate`
3. Configure database (`DB_*`), Redis (`REDIS_*`), mail (`MAIL_*`).
4. **Docker:** all host ports via `.env` only (`HOST_HTTP_PORT`, `HOST_POSTGRES_PORT`, …). See [CONFIGURATION.md](../CONFIGURATION.md).
5. Create owner: `php artisan app:bootstrap` (`SEED_OWNER_*`).
6. Production: `APP_DEBUG=false`, strong passwords, `PAYMENT_GATEWAY=http`, HTTPS — see [PRODUCTION_DEPLOY.md](../PRODUCTION_DEPLOY.md).

---

## Post-install verification

```bash
curl -fsS "http://localhost:${HOST_HTTP_PORT:-80}/health"
curl -fsS "http://localhost:${HOST_HTTP_PORT:-80}/up"
```

Expected: HTTP **200** and JSON probe status.

Optional checks:

```bash
docker compose exec php php artisan about
docker compose exec php composer check:php
npm run build
```

| URL | Purpose |
|-----|---------|
| `/` | Home |
| `/posts` | Post list |
| `/admin` | Filament (owner) |
| `/docs/api` | OpenAPI (Scramble) |
| `/health` | Readiness |
| `/up` | Liveness |

---

## Channel guides

- [DOCKER.md](DOCKER.md) — dev and production Compose, AI profile, Mailpit
- [LINUX.md](LINUX.md) — native Linux/macOS (PHP-FPM, Nginx, systemd)
- [WINDOWS.md](WINDOWS.md) — native PHP, Laragon, Docker Desktop, WSL2
- [KUBERNETES.md](KUBERNETES.md) — Helm chart, Secrets, probes, scaling

---

## Optional Docker profiles

| Profile | Command | Purpose |
|---------|---------|---------|
| `ai` | `docker compose --profile ai up -d ai-service` | Python FastAPI worker |
| `prod` | `docker compose --profile prod up -d queue` | Queue worker in dev stack |
| `install` | see DOCKER.md (prod compose) | One-shot migrate + bootstrap |

AI variables: `AI_SERVICE_URL`, `AI_SERVICE_DATABASE_URL`, `HOST_AI_SERVICE_PORT`, `OPENAI_*` — [CONFIGURATION.md](../CONFIGURATION.md).

---

## Owner bootstrap

Install scripts run `php artisan app:bootstrap --force` using:

```
SEED_OWNER_EMAIL=
SEED_OWNER_PASSWORD=
SEED_OWNER_NAME=
```

Production rejects the password `password` via config guard.

---

## License and commercial use

Commercial deployment and integration are **allowed**; standalone product resale is **not**.  
See [LICENSE](../../LICENSE) · [COMMERCIAL_SERVICES.md](../COMMERCIAL_SERVICES.md).
