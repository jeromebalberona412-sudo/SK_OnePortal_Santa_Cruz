#!/bin/sh
set -e

echo "Starting Laravel application entrypoint..."

# ============================================
# Set proper permissions
# ============================================
echo "Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chown -R www-data:www-data /var/www/html/public/storage
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/public/storage

# ============================================
# Create storage directories if missing
# ============================================
echo "Ensuring storage directories exist..."
mkdir -p /var/www/html/storage/framework/{views,cache,sessions}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache

# ============================================
# Handle storage link (idempotent)
# ============================================
echo "Setting up storage link..."
if [ -L "/var/www/html/public/storage" ]; then
    echo "Storage symlink already exists, skipping creation"
elif [ -d "/var/www/html/public/storage" ]; then
    echo "Storage exists as a directory, removing and recreating as symlink"
    rm -rf /var/www/html/public/storage
    php artisan storage:link || echo "Storage link creation failed"
else
    echo "Creating storage symlink"
    php artisan storage:link || echo "Storage link creation failed"
fi

# ============================================
# Run package:discover (skipped during composer install)
# ============================================
echo "Running package:discover..."
php artisan package:discover --ansi || echo "Package discovery failed or already run"

# ============================================
# Wait for database if DB_HOST is set
# ============================================
if [ -n "$DB_HOST" ]; then
    echo "Waiting for database connection..."
    max_attempts=30
    attempt=0
    while [ $attempt -lt $max_attempts ]; do
        if nc -z "$DB_HOST" 5432; then
            echo "Database is ready!"
            break
        fi
        attempt=$((attempt + 1))
        echo "Waiting for database... (attempt $attempt/$max_attempts)"
        sleep 2
    done
    if [ $attempt -eq $max_attempts ]; then
        echo "Warning: Database connection not established after $max_attempts attempts"
    fi
fi

# ============================================
# Run migrations if enabled
# ============================================
if [ "$RUN_MIGRATIONS" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || echo "Migrations failed or already run"
fi

# ============================================
# Clear and cache configurations
# ============================================
echo "Optimizing application..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan event:clear

# Only cache if APP_KEY is set (not during image build)
if [ -n "$APP_KEY" ]; then
    echo "Caching configurations..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
else
    echo "APP_KEY not set, skipping config caching"
fi

# ============================================
# Optimize Composer autoloader
# ============================================
echo "Optimizing Composer autoloader..."
composer dump-autoload --optimize || echo "Composer optimization failed"

# ============================================
# Configure Nginx to use Render PORT
# ============================================
if [ -n "$PORT" ]; then
    echo "Configuring Nginx to use port $PORT..."
    sed -i "s/listen 8080;/listen $PORT;/g" /etc/nginx/nginx.conf
else
    echo "PORT not set, using default 8080"
fi

echo "Starting services..."
exec "$@"
