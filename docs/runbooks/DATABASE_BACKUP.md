# Runbook: Database backup & restore

**Command:** `php artisan db:backup`  
**Output:** `storage/backups/backup-{connection}-{timestamp}.sql`

## Prerequisites

- PostgreSQL client tools (`pg_dump`, `psql`) installed on the host or in the PHP container.
- Database credentials from `.env` (`DB_*`).

## Backup (manual)

```bash
# Docker
docker compose exec php php artisan db:backup

# Native
php artisan db:backup --path=storage/backups
```

Optional: `--connection=pgsql` to target a non-default connection.

## Restore

```bash
# Stop writers (maintenance mode) before restore in production
php artisan down

PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -f storage/backups/backup-pgsql-YYYYMMDD-HHMMSS.sql

php artisan up
```

## Scheduling

Add to `routes/console.php` or cron:

```php
Schedule::command('db:backup')->dailyAt('02:30');
```

Copy backups off-site (S3, NAS) — not included in this project.

## Verification

1. File size > 0 bytes.
2. `php artisan db:show` reports expected tables.
3. `/health` returns `database: ok`.

## Failure modes

| Symptom | Action |
|---------|--------|
| `pg_dump: command not found` | Install PostgreSQL client in container/host |
| Permission denied on `storage/backups` | `chmod 775 storage/backups` |
| Connection refused | Check `DB_HOST` / Docker network |
