#!/bin/sh
set -e

cd /var/www/html

mkdir -p storage/framework/views bootstrap/cache
chown -R www-data:www-data storage/framework/views bootstrap/cache 2>/dev/null || true
chmod -R 775 storage/framework/views bootstrap/cache 2>/dev/null || true

if [ "${APP_AUTO_BOOTSTRAP:-true}" = "true" ]; then
  php artisan migrate --force
  php artisan storage:link --force 2>/dev/null || true
  php artisan app:bootstrap
fi

chown -R www-data:www-data storage/framework/views bootstrap/cache 2>/dev/null || true
chmod -R 775 storage/framework/views bootstrap/cache 2>/dev/null || true

exec docker-php-entrypoint php-fpm
