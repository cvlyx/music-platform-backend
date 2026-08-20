#!/bin/sh
set -e

php artisan key:generate --force 2>/dev/null || true
php artisan migrate --force

# Only seed if the users table is empty
SEED=$(php artisan tinker --execute="echo \App\Models\User::count() === 0 ? 'yes' : 'no';" 2>/dev/null)
if echo "$SEED" | grep -q "yes"; then
    php artisan db:seed --force
fi

php artisan serve --host=0.0.0.0 --port=$PORT
