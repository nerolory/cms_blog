# Production deploy — чеклист оператора

**Статус кода:** готов к деплою (Docker prod, guards, `.env.production.example`).  
**Ниже — действия вне репозитория** (secrets, TLS, GitHub settings).

Связанные документы: [`CI.md`](CI.md) · [`TURNKEY_OPTIMIZATION_PLAN.md`](TURNKEY_OPTIMIZATION_PLAN.md) · [`runbooks/DATABASE_BACKUP.md`](runbooks/DATABASE_BACKUP.md)

---

## 1. GitHub (до первого merge в main)

| Шаг | Действие |
|-----|----------|
| Branch protection | [`CI.md`](CI.md) § Branch protection — required checks |
| Secrets (Actions) | Только если нужны deploy workflows; для quality CI secrets не требуются |
| Environments | `production` с required reviewers — при наличии CD |

**Required status checks:** `php-quality`, `python-quality`, `php-tests`, `php-tests-pgsql`, `frontend-quality`, `e2e-smoke`.

---

## 2. Secrets и `.env` production

Скопировать [`.env.production.example`](../.env.production.example) → `.env` на сервере / в secret store.

| Переменная | Обязательно | Примечание |
|------------|-------------|------------|
| `APP_KEY` | ✅ | `php artisan key:generate` на prod **один раз** |
| `APP_URL` | ✅ | Публичный HTTPS URL |
| `APP_DEBUG` | ✅ | `false` |
| `DB_PASSWORD` | ✅ | Не дефолт из example |
| `REDIS_PASSWORD` | ✅ | Если Redis с auth |
| `SEED_OWNER_PASSWORD` | ✅ bootstrap | ≠ `password`; guard `ProductionConfigGuard` |
| `HEALTH_ALLOWED_IPS` | ✅ | CSV IP/CIDR мониторинга; guard при boot |
| `PAYMENT_HTTP_URL` / `PAYMENT_HTTP_SECRET` | при `PAYMENT_GATEWAY=http` | Mock отключён в prod |
| `PAYMENT_WEBHOOK_SECRET` | при `PAYMENT_GATEWAY=http` | HMAC webhook; guard при boot |
| `MAIL_*` | ✅ | SMTP TLS (`MAIL_SCHEME=tls`) |
| `SESSION_SECURE_COOKIE` | ✅ | `true` за HTTPS |

**Не коммитить:** `.env`, ключи API, payment secrets, backup credentials.

---

## 3. TLS и reverse proxy

| Шаг | Действие |
|-----|----------|
| TLS termination | nginx / Caddy / cloud LB → upstream `docker-compose.prod` |
| HSTS | `SECURITY_HSTS_ENABLED=true` в `.env` (после проверки HTTPS) |
| Cookies | `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true` |
| Trusted proxies | `TrustProxies` / `APP_URL` согласован с LB |

---

## 4. Deploy (Docker prod)

```bash
docker compose -f docker-compose.prod.yml --profile install run --rm install
docker compose -f docker-compose.prod.yml --profile prod up -d
```

Post-deploy:

1. `php artisan migrate --force` (если не в install profile)
2. Site Health в Filament → critical = 0
3. k6 smoke — [`OPERATIONS.md`](OPERATIONS.md)
4. Off-site backup — [`runbooks/DATABASE_BACKUP.md`](runbooks/DATABASE_BACKUP.md)

---

## 5. Go-live gate

- [ ] Branch protection на `main`
- [ ] Все CI jobs green на release commit
- [ ] Secrets заполнены, `APP_DEBUG=false`
- [ ] TLS + HSTS
- [ ] `HEALTH_ALLOWED_IPS` настроен
- [ ] Redis cache/session/queue
- [ ] Backup расписание + off-site copy
- [ ] k6 smoke PASS на staging/prod URL

---

## Оценка готовности (аудит)

| Область | Оценка | Комментарий |
|---------|--------|-------------|
| **Tech Lead (процесс)** | **94%** | CI multi-stack, guards, docs, local `make ci` |
| **Tech Lead (prod deploy)** | **92%** | Код и compose готовы; §1–3 — оператор |
