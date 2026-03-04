#!/bin/sh
set -e

cd /var/www/admin-service

if [ "$(id -u)" = "0" ]; then
  mkdir -p storage bootstrap/cache
  chown -R www-data:www-data storage bootstrap/cache
fi

php artisan optimize || true

exec php-fpm