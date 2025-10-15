#!/bin/sh
set -e

# ensure correct ownership for storage & cache
if [ "$(id -u)" = '0' ]; then
  chown -R www-data:www-data /var/www/html || true
fi

# make sure storage and bootstrap cache dirs exist
mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 0775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# run artisan migrate if RUN_MIGRATIONS env var set to "true"
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
  # wait for DB to be ready? minimal attempt; in Coolify you can control timing with healthchecks
  echo "Running migrations..."
  php /var/www/html/artisan migrate --force || echo "Migrations failed or DB not ready"
fi

# run artisan cache:clear & config:cache if requested
if [ "${CACHE_CLEAR_ON_START:-false}" = "true" ]; then
  php /var/www/html/artisan config:cache || true
  php /var/www/html/artisan route:cache || true
  php /var/www/html/artisan view:cache || true
fi

# exec the container's main process (php-fpm)
exec "$@"
