#!/bin/bash

echo "🚀 Starting Laravel API deployment on Railway..."

# Set permissions
chmod -R 755 storage bootstrap/cache

# Clear and cache configurations
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Generate app key if not exists
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Seed database if needed (only if tables are empty)
php artisan db:seed --force

# Cache configurations for production
php artisan config:cache
php artisan route:cache

# Start the application
echo "✅ Laravel API ready on Railway!"
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}