#!/bin/sh
set -e


# Create the public/storage symlink if it doesn't exist yet.
if [ ! -L /var/www/public/storage ]; then
    php artisan storage:link
fi

php artisan optimize:clear


php artisan migrate --force

exec php artisan octane:frankenphp --port=80
