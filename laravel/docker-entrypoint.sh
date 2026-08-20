#!/bin/bash
set -e

# Configure port dynamically if provided by Render
if [ -n "$PORT" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# Ensure .env exists with solid production defaults
if [ ! -f /var/www/html/.env ]; then
    cp /var/www/html/.env.example /var/www/html/.env || touch /var/www/html/.env
fi

# Ensure SQLite, File Session & Cache in .env
sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=sqlite/' /var/www/html/.env || echo "DB_CONNECTION=sqlite" >> /var/www/html/.env
sed -i 's/^SESSION_DRIVER=.*/SESSION_DRIVER=file/' /var/www/html/.env || echo "SESSION_DRIVER=file" >> /var/www/html/.env
sed -i 's/^CACHE_STORE=.*/CACHE_STORE=file/' /var/www/html/.env || echo "CACHE_STORE=file" >> /var/www/html/.env
sed -i 's/^QUEUE_CONNECTION=.*/QUEUE_CONNECTION=sync/' /var/www/html/.env || echo "QUEUE_CONNECTION=sync" >> /var/www/html/.env

# Ensure database directory and file exist with full write permissions
mkdir -p /var/www/html/database /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/framework/cache
touch /var/www/html/database/database.sqlite
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

# Ensure APP_KEY exists
php artisan key:generate --force || true

# Run database migrations and seed admin
php artisan migrate --force || true
php artisan db:seed --class=AdminUserSeeder --force || true
php artisan storage:link || true

# Cache configs
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

exec "$@"
