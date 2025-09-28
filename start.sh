#!/bin/bash

echo "🚀 Starting Laravel API deployment on Railway..."

# Set default port if not provided
export PORT=${PORT:-8000}

# Set permissions
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate app key if not exists
php artisan key:generate --force --no-interaction

# Run database migrations
php artisan migrate --force --no-interaction

# Seed database if needed (only if tables are empty)
php artisan db:seed --force --no-interaction || echo "Seeding failed or not needed"

# Cache configurations for production
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction

# Start the application
echo "✅ Laravel API ready on Railway at port $PORT!"
exec php artisan serve --host=0.0.0.0 --port=$PORT