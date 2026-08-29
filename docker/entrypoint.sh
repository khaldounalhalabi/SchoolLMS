#!/bin/sh
set -e

# --------------------------------------------------
# Fix ownership of mounted volumes (named volumes keep
# whatever owner they were created with — the image's
# build-time chown doesn't apply to them)
# --------------------------------------------------
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# --------------------------------------------------
# Web container initialization
# --------------------------------------------------
echo "Initializing Laravel..."

rm -f \
    bootstrap/cache/packages.php \
    bootstrap/cache/services.php \
    bootstrap/cache/config.php \
    bootstrap/cache/routes-v7.php

if [ ! -L public/storage ]; then
    su-exec www-data php artisan storage:link
fi

php artisan package:discover --ansi

php artisan optimize:clear

echo "Running database migrations..."
php artisan migrate --force

echo "Starting Laravel Octane with FrankenPHP..."
exec php artisan octane:frankenphp --port=80
