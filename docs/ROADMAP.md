# Roadmap

**Russian:** [docs/ru/ROADMAP.md](ru/ROADMAP.md)

The roadmap is split into **closed** (implemented) and **open** (planned).  
Quality status: [`QUALITY.md`](QUALITY.md) · deployment: [`PRODUCTION_DEPLOY.md`](PRODUCTION_DEPLOY.md).

---

## Closed (implemented)

### Application core

- Laravel 13, PHP 8.3+, **Controller → Service (contract) → Repository**
- Post CRUD (web + Sanctum API), moderation, versions, signed preview URLs
- RBAC (Spatie + Filament Shield), policies, post visibility
- SEO: meta, JSON-LD, sitemap, robots.txt, conditional GET / ETag
- Comments, reactions, search (PostgreSQL FTS + fallback)
- Tokens, mock/HTTP payment gateway, payment webhook
- AI: analysis orders, Python FastAPI worker (`ai` profile)
- Filament 4 admin, health probes, maintenance mode

### Quality and security

- PHPStan L9 (0 errors), Pint, PHPDoc gates, line-length 120
- PHPUnit (200+), Playwright E2E, Vitest + TypeScript for frontend lib
- IDOR/XSS/CSRF/throttle, CSP nonce, production config guards
- W3C Nu Validator — 0 errors on public pages

### Infrastructure

- Docker Compose (dev + prod profiles), Helm chart skeleton
- CI: PHP, Python, frontend, PG integration, E2E
- Install scripts: `make install`, `scripts/install.sh`, `scripts/install.ps1`
- Install docs: native Linux/Windows, Docker, Kubernetes

---

## Open (planned)

### Product

- [ ] Full Stripe/YooKassa adapter (current: HTTP gateway + webhook contract)
- [ ] Elasticsearch / Solr / Sphinx search profiles (commented in compose)
- [ ] Extended AI: streaming, multiple LLM providers in UI
- [ ] Multi-tenant / multi-site (current: single-tenant)

### Frontend

- [ ] Full migration of entrypoints to TypeScript
- [ ] E2E: avatar crop, TinyMCE dirty-state, admin smoke
- [ ] Storybook / design system on Bootstrap 5

### Kubernetes / enterprise

- [ ] Production-ready Helm: Secrets, init Job, PVC, HPA, cert-manager
- [ ] Observability stack (Prometheus metrics, centralized logs)
- [ ] GitOps deploy manifests (Argo CD / Flux) — outside MVP scope

### Documentation and release

- [ ] Portfolio video walkthrough
- [ ] Published Docker images on a registry

---

## Out of repository scope (operator)

- GitHub branch protection, production secrets, TLS, off-site backup — [`PRODUCTION_DEPLOY.md`](PRODUCTION_DEPLOY.md)
