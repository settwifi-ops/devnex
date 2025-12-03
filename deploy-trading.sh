#!/bin/bash
# deploy-trading.sh
# Script untuk deploy trading system

echo "🚀 Deploying Trading System..."

# 1. Pull latest code
cd /var/www/devnex
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Update database
php artisan migrate --force

# 4. Clear caches
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Update supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# 6. Restart all workers
sudo supervisorctl restart all

# 7. Clear trading cache (opsional)
php artisan trading:cache-clear

echo "✅ Deployment completed!"
