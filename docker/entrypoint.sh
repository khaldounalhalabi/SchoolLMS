#!/bin/sh
set -e

# --------------------------------------------------
# Fix ownership of mounted volumes (named volumes keep
# whatever owner they were created with — the image's
# build-time chown doesn't apply to them)
# --------------------------------------------------
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# --------------------------------------------------
# Worker / scheduler containers
# --------------------------------------------------
# Only queue:work / schedule:work take the early-exit path.
# The app container's default CMD (octane:frankenphp) also
# starts with "php artisan", so we must check the actual
# subcommand, not just $1.
# --------------------------------------------------
if [ "$1" = "php" ] && { [ "$3" = "queue:work" ] || [ "$3" = "schedule:work" ]; }; then
    exec su-exec www-data "$@"
fi

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

su-exec www-data php artisan package:discover --ansi
su-exec www-data composer run add-tcpdf-fonts

su-exec www-data php artisan config:cache
su-exec www-data php artisan route:cache
su-exec www-data php artisan view:cache

echo "Running database migrations..."
su-exec www-data php artisan migrate --force

echo "Starting Laravel Octane with FrankenPHP..."
exec su-exec www-data php artisan octane:frankenphp --port=80
