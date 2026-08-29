#!/bin/sh
set -e

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

if [ "$1" != "nginx-php-fpm" ]; then
    exec su-exec www-data "$@"
fi

echo "Initializing Laravel..."

rm -f \
    bootstrap/cache/packages.php \
    bootstrap/cache/services.php \
    bootstrap/cache/config.php \
    bootstrap/cache/routes-v7.php \
    bootstrap/cache/routes.php

if [ ! -L public/storage ]; then
    su-exec www-data php artisan storage:link
fi

su-exec www-data php artisan package:discover --ansi
su-exec www-data php artisan optimize:clear

echo "Running database migrations..."
su-exec www-data php artisan migrate --force

su-exec www-data php artisan optimize

echo "Starting PHP-FPM and nginx..."
php-fpm -D
exec nginx -g "daemon off;"
