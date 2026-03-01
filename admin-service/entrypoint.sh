#!/bin/sh
set -e

cd /var/www/admin-service

if [ "$(id -u)" = "0" ]; then
  mkdir -p storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache
fi

if [ "${APP_ENV}" = "production" ]; then
  php artisan config:cache || true
  php artisan route:cache || true
  php artisan view:cache || true
fi

exec php-fpm