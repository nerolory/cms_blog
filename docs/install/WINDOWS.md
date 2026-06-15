# Windows install

**Russian:** [docs/ru/install/WINDOWS.md](../ru/install/WINDOWS.md)

Deployment on Windows: Docker Desktop (recommended), WSL2, Laragon, or standalone PHP.  
Configuration: [`../CONFIGURATION.md`](../CONFIGURATION.md) · overview: [`INSTALL.md`](INSTALL.md).

---

## Recommended: Docker Desktop

The most predictable path on Windows is the same stack as CI/Linux.

### Prerequisites

- Windows 10/11 64-bit
- [Docker Desktop](https://www.docker.com/products/docker-desktop/) with **WSL2 backend**
- Git for Windows
- Node.js 20+ on **host** (for `npm run dev` / `npm run build`)

### Steps

```powershell
git clone <repository-url> cms-blog
cd cms-blog
Copy-Item .env.example .env
# Edit .env: DB_CONNECTION=pgsql, DB_HOST=database (see CONFIGURATION.md)
docker compose up -d --build
docker compose exec php composer setup
npm install
npm run build
```

Verify:

```powershell
curl.exe -fsS "http://localhost/health"
```

Full Docker details: [DOCKER.md](DOCKER.md).

> **Important:** Run `npm install` / `npm run dev` on the **Windows host**, not inside the Linux PHP container — bind mounts on Docker Desktop break file watchers and utime. See project `Makefile` comment `ci-frontend`.

---

## One-click script (native or hybrid)

```powershell
git clone <repository-url> cms-blog
cd cms-blog
make install
# or
powershell -ExecutionPolicy Bypass -File scripts\install.ps1
```

The script mirrors Linux install: `.env`, composer, migrate, bootstrap, npm build, health check.

---

## Native PHP (Laragon / XAMPP / standalone)

### Prerequisites

- PHP 8.3+ with extensions: `pdo_sqlite` or `pdo_pgsql`, `mbstring`, `openssl`, `fileinfo`, `curl`
- Composer (global or `composer.phar`)
- Node.js 20+
- PostgreSQL (optional; SQLite for quick start)

### Laragon quick start

1. Install [Laragon](https://laragon.org/) with PHP 8.3
2. Clone repo into `C:\laragon\www\cms-blog`
3. Copy `.env.example` → `.env`
4. For SQLite:

   ```dotenv
   DB_CONNECTION=sqlite
   ```

   Create file: `New-Item database\database.sqlite -ItemType File`

5. Run install script:

   ```powershell
   powershell -ExecutionPolicy Bypass -File scripts\install.ps1
   ```

6. Add virtual host in Laragon → `cms-blog.test` pointing to `public/`

### Manual commands

```powershell
composer install
php artisan key:generate
php artisan migrate
php artisan app:bootstrap
npm ci
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

### Enable pdo_pgsql

Edit `php.ini` (Laragon: Menu → PHP → php.ini):

```ini
extension=pdo_pgsql
extension=pgsql
```

Restart Apache/nginx in Laragon.

---

## WSL2 native (without Docker for app)

Use Ubuntu in WSL2 and follow [LINUX.md](LINUX.md) inside WSL.  
Access from Windows browser via WSL IP or `localhost` port forwarding.

```bash
# Inside WSL
cd ~/cms-blog
bash scripts/install.sh
php artisan serve --host=0.0.0.0 --port=8000
```

Run `npm run dev` in WSL or on Windows host against the same project path (`\\wsl$\Ubuntu\...` — prefer WSL terminal for npm).

---

## Environment variables (Windows-specific notes)

Use forward slashes or escaped paths in `.env` for SQLite:

```dotenv
DB_DATABASE=C:/laragon/www/cms-blog/database/database.sqlite
```

All Docker host ports still come from `.env` when using Compose:

```dotenv
HOST_HTTP_PORT=8080
HOST_POSTGRES_PORT=5433
HOST_VITE_PORT=5173
HOST_AI_SERVICE_PORT=8100
```

See full list: [`../CONFIGURATION.md`](../CONFIGURATION.md).

---

## Production on Windows Server

For production prefer:

1. **Linux VM / cloud** with [LINUX.md](LINUX.md) or Docker prod compose, or
2. **IIS + PHP** (advanced) — mirror Nginx rules from LINUX.md for `public/index.php`

Minimum production `.env` (use `.env.production.example`):

- `APP_DEBUG=false`
- `APP_ENV=production`
- Strong `SEED_OWNER_PASSWORD`
- `REDIS_PASSWORD` if Redis exposed

Checklist: [`../PRODUCTION_DEPLOY.md`](../PRODUCTION_DEPLOY.md).

---

## Verification

```powershell
curl.exe -fsS http://127.0.0.1:8000/health
docker compose exec php php artisan about   # if using Docker
npm run check:frontend                      # on host
```

---

## Troubleshooting

| Problem | Solution |
|---------|----------|
| `ExecutionPolicy` blocks script | `-ExecutionPolicy Bypass` |
| Docker volume slow / Blade errors | Use provided named volumes in compose (already configured) |
| `npm` EACCES in container | Run npm on Windows host |
| Port 80 in use | Set `HOST_HTTP_PORT=8080` in `.env`, recreate containers |
| curl health fails before server up | Start `docker compose up` or `php artisan serve` first |
| Line ending issues | Repo uses LF; configure Git `core.autocrlf=true` on Windows |

---

## Related

- [DOCKER.md](DOCKER.md) — full Compose guide
- [LINUX.md](LINUX.md) — WSL / server deployment
- [KUBERNETES.md](KUBERNETES.md) — cluster deployment
