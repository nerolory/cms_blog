#!/usr/bin/env bash
# Benchmark bind-mount I/O vs Laravel bootstrap (run: docker compose exec php bash /var/www/html/scripts/bench-mount.sh)
set -euo pipefail

echo "=== Mount ==="
df -T /var/www/html | tail -1
mount | grep '/var/www/html' || true

echo
echo "=== Autoload (bind mount) ==="
php -r '$s=microtime(true); require "/var/www/html/vendor/autoload.php"; echo round((microtime(true)-$s)*1000)." ms\n";'

echo
echo "=== Stat loop x500 (bind mount) ==="
php -r '$s=microtime(true); for($i=0;$i<500;$i++){ clearstatcache(); stat("/var/www/html/vendor/autoload.php"); } echo round((microtime(true)-$s)*1000)." ms\n";'

echo
echo "=== Copy vendor to /tmp and autoload (native ext4) ==="
rm -rf /tmp/vendor-bench
cp -a /var/www/html/vendor /tmp/vendor-bench
php -r '$s=microtime(true); require "/tmp/vendor-bench/autoload.php"; echo round((microtime(true)-$s)*1000)." ms\n";'

echo
echo "=== Laravel bootstrap (artisan inspire) ==="
cd /var/www/html
START=$(date +%s%N)
php artisan inspire --quiet
END=$(date +%s%N)
echo "$(( (END - START) / 1000000 )) ms"
