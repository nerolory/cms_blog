# Операции и нагрузочное тестирование

## Поиск (production)

**Обязательно:** `DB_CONNECTION=pgsql` в production.

- Полнотекстовый поиск (`tsvector` + GIN) работает только на PostgreSQL; см. [`DATABASE_SCHEMA.md`](DATABASE_SCHEMA.md).
- Fallback `LIKE '%term%'` на SQLite/MySQL даёт full table scan — допустим только для локальной разработки.
- Минимальная длина поискового запроса задаётся валидацией `SearchFilters` и формой поиска.

## k6 smoke

Скрипт: [`scripts/k6/smoke.js`](../scripts/k6/smoke.js)

Проверяет доступность `/`, `/health`, `/posts` при лёгкой нагрузке (5 VU, 30s).

### Требования

- [k6](https://k6.io/docs/get-started/installation/) на хосте или в CI runner

### Локальный прогон

```bash
# Laravel должен быть запущен
php artisan serve --host=127.0.0.1 --port=8000

# В другом терминале
k6 run scripts/k6/smoke.js
```

С кастомным URL:

```bash
BASE_URL=https://staging.example.com k6 run scripts/k6/smoke.js
```

### Пороги (thresholds)

| Метрика | Порог |
|---------|-------|
| `http_req_failed` | < 5% |
| `http_req_duration` p(95) | < 2000 ms |

### Production Docker

После `docker compose -f docker-compose.prod.yml --profile prod up -d`:

```bash
BASE_URL=http://localhost k6 run scripts/k6/smoke.js
```

## Связанные документы

- [`TURNKEY_OPTIMIZATION_PLAN.md`](TURNKEY_OPTIMIZATION_PLAN.md) — production checklist (оператор)
- [`runbooks/DATABASE_BACKUP.md`](runbooks/DATABASE_BACKUP.md) — резервное копирование
- [`HTTP_CACHE.md`](HTTP_CACHE.md) — кэширование статики
