#!/bin/bash

echo "🚀 Starting Laravel API on Railway..."

# Set permissions for storage
chmod -R 777 storage bootstrap/cache

# Generate application key if needed
php artisan key:generate --force --no-interaction || echo "Key already exists"

# Run database migrations
php artisan migrate --force --no-interaction || echo "Migration failed"

# Seed database (optional, only if needed)
php artisan db:seed --force --no-interaction || echo "Seeding skipped"

# Clear and cache only what's needed for API
php artisan config:cache --no-interaction || echo "Config cache failed"
php artisan route:cache --no-interaction || echo "Route cache failed"

echo "✅ Laravel API ready!"
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}