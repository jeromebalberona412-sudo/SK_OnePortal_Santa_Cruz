#!/bin/sh
set -e

# Wait for database if needed
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection..."
    while ! nc -z "$DB_HOST" 5432; do
        sleep 1
    done
    echo "Database is ready!"
fi

# Run migrations if enabled
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Link storage if not already linked
if [ ! -L "public/storage" ]; then
    php artisan storage:link
fi

# Clear and cache configs for production
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimize composer
composer dump-autoload --optimize

# Ensure proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache

echo "Starting services..."
exec "$@"
