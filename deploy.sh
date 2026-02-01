#!/bin/bash
#
# BEKASEMWEB - CPanel Git Deployment Script
# ==========================================
# 
# This script is designed for CPanel's Git Version Control deployment.
# Place this script at the root of your repository.
#
# Setup Instructions:
# 1. In CPanel, go to Git Version Control
# 2. Create repository with clone URL from GitHub
# 3. Set document root to: public_html/api (or your preferred path)
# 4. In .cpanel.yml, reference this script
#

# Exit on any error
set -e

echo "🚀 Starting BEKASEMWEB Backend Deployment..."

# Navigate to deployment directory
DEPLOY_DIR="/home/username/public_html/api"
cd $DEPLOY_DIR

# Install composer dependencies (production)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Clear and optimize
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs

echo "✅ Deployment completed successfully!"
