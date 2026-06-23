#!/bin/sh

cd /var/www/html

mkdir -p storage/framework/views storage/app/private storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

wait_for_database() {
  echo "Waiting for database..."
  attempt=0
  while [ "$attempt" -lt 60 ]; do
    if php -r '
      $host = getenv("DB_HOST") ?: "database";
      $port = getenv("DB_PORT") ?: "5432";
      $database = getenv("DB_DATABASE") ?: "laravel";
      $username = getenv("DB_USERNAME") ?: "postgres";
      $password = getenv("DB_PASSWORD") ?: "";
      try {
          new PDO(
              "pgsql:host={$host};port={$port};dbname={$database}",
              $username,
              $password,
              [PDO::ATTR_TIMEOUT => 2],
          );
          exit(0);
      } catch (Throwable) {
          exit(1);
      }
    '; then
      echo "Database is ready."

      return 0
    fi
    attempt=$((attempt + 1))
    sleep 2
  done

  echo "Warning: database not reachable after 120s."

  return 1
}

if [ "${APP_AUTO_BOOTSTRAP:-true}" = "true" ]; then
  wait_for_database || true
  php artisan migrate --force || echo "Warning: migrate failed, starting php-fpm anyway."
  php artisan storage:link --force 2>/dev/null || true
  php artisan app:bootstrap || echo "Warning: app:bootstrap failed, starting php-fpm anyway."
fi

php artisan view:clear 2>/dev/null || echo "Warning: view:clear failed."

chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

exec docker-php-entrypoint php-fpm
