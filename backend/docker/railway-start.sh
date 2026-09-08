#!/bin/sh
set -e

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan migrate --force

# Idempotent (DemoUserSeeder/DemoDataSeeder use updateOrCreate and skip
# links that already have clicks) - safe to run on every deploy, not just
# the first one.
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
