**English:** [docs/install/LINUX.md](../../install/LINUX.md)

Нативная установка без Docker: PHP-FPM или `artisan serve`, PostgreSQL, Redis, Nginx/Apache.  
Конфигурация: [CONFIGURATION.md](../CONFIGURATION.md) · обзор: [INSTALL.md](INSTALL.md).

---

## Prerequisites

| Component | Version / notes |
|-----------|-----------------|
| PHP | 8.3+ |
| Extensions | `pdo`, `pdo_pgsql` (or `pdo_sqlite`), `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl` |
| Composer | 2.x |
| Node.js | 20+ and npm |
| PostgreSQL | 15+ (recommended) or SQLite for quick local dev |
| Redis | 7+ (required for production queue/cache) |
| Web server | Nginx or Apache (production); `php artisan serve` (dev only) |

### Debian / Ubuntu packages (example)

```bash
sudo apt update
sudo apt install -y php8.3-cli php8.3-fpm php8.3-pgsql php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-bcmath php8.3-redis postgresql redis-server nginx
```

### macOS (Homebrew)

```bash
brew install php@8.3 composer node postgresql@16 redis nginx
brew services start postgresql@16
brew services start redis
```

---

## One-click install

```bash
git clone <repository-url> cms-blog
cd cms-blog
make install
# or
bash scripts/install.sh
```

Скрипт выполняет:

1. Проверку prerequisites (`php`, `composer`, `node`, `npm`)
2. Создание `.env` из `.env.production.example` или `.env.example`
3. `composer install`
4. `php artisan key:generate`
5. `php artisan migrate --force`
6. `php artisan app:bootstrap --force`
7. `npm ci && npm run build`
8. Health gate на `http://localhost/health` (если веб уже доступен)

---

## Manual install (step by step)

### 1. Clone and dependencies

```bash
git clone <repository-url> cms-blog && cd cms-blog
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Database

**PostgreSQL:**

```bash
sudo -u postgres createuser -P laravel
sudo -u postgres createdb -O laravel laravel
```

`.env`:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=your-secret
```

**SQLite (quick dev only):**

```dotenv
DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite
```

```bash
touch database/database.sqlite
```

### 3. Redis (recommended)

```dotenv
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### 4. Migrate and bootstrap

```bash
php artisan migrate
php artisan app:bootstrap
```

Set `SEED_OWNER_EMAIL`, `SEED_OWNER_PASSWORD`, `SEED_OWNER_NAME` in `.env` before bootstrap.

### 5. Frontend

```bash
npm ci
npm run build
```

Dev server: `npm run dev` (separate terminal).

### 6. Run application

**Development:**

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Open `http://127.0.0.1:8000`.

**Production — PHP-FPM + Nginx**

Example Nginx server block (`/etc/nginx/sites-available/demo`):

```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/cms-blog/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/demo /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### 7. Queue worker (production)

Systemd unit `/etc/systemd/system/demo-queue.service`:

```ini
[Unit]
Description=CMS Blog Queue Worker
After=network.target redis.service postgresql.service

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/cms-blog/artisan queue:work --sleep=3 --tries=3 --max-time=3600
WorkingDirectory=/var/www/cms-blog

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable --now demo-queue
```

### 8. Scheduler (optional)

Crontab for `www-data`:

```cron
* * * * * cd /var/www/cms-blog && php artisan schedule:run >> /dev/null 2>&1
```

---

## Production hardening

Follow [`../../PRODUCTION_DEPLOY.md`](../../PRODUCTION_DEPLOY.md):

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R ug+rwx storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Set in `.env`: `APP_DEBUG=false`, `APP_ENV=production`, HTTPS `APP_URL`, strong passwords, `PAYMENT_GATEWAY=http`.

---

## Optional: AI service (native)

Run Python worker separately (see `ai-service/README.md` if present):

```bash
cd ai-service
python -m venv .venv && source .venv/bin/activate
pip install -r requirements.txt
export DATABASE_URL="postgresql://laravel:secret@127.0.0.1:5432/laravel"
uvicorn app.main:app --host 0.0.0.0 --port 8100
```

Laravel `.env`: `AI_SERVICE_URL=http://127.0.0.1:8100`.

---

## Verification

```bash
curl -fsS http://127.0.0.1:8000/health   # or your domain
php artisan about
composer check:php
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `pdo_pgsql` missing | Install `php-pgsql` / enable in `php.ini` |
| Permission denied `storage/` | `chmod -R ug+rwx storage bootstrap/cache` |
| 500 after deploy | `php artisan config:clear`, check `storage/logs/laravel.log` |
| Queue jobs stuck | Ensure Redis running and `demo-queue` service active |
| Vite assets 404 | Run `npm run build`; set `APP_URL` correctly |

---

## Docker alternative

If native setup is heavy, use [DOCKER.md](DOCKER.md) — recommended for parity with CI.
