#!/bin/bash
set -e

# Configure port dynamically if provided by Render
if [ -n "$PORT" ]; then
    sed -i "s/80/$PORT/g" /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf
fi

# Ensure all framework storage and upload folders exist
mkdir -p /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache \
         /var/www/html/database \
         /var/www/html/public/uploads/customers \
         /var/www/html/public/uploads/products \
         /var/www/html/public/uploads/materials

# Run database migrations and seed admin
php artisan key:generate --force || true
php artisan migrate --force || true
php artisan db:seed --class=AdminUserSeeder --force || true
php artisan db:seed --class=StaffAndPayrollSeeder --force || true
php artisan db:seed --class=DyeAndAssetSeeder --force || true
php artisan materials:sync-api --force || true
php artisan storage:link || true

# Final permission lock ensuring Apache www-data has full read/write access to SQLite & storage
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads
chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database /var/www/html/public/uploads
chmod 666 /var/www/html/database/database.sqlite || true

exec "$@"
