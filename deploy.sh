#!/bin/bash
#
# BEKASEMWEB - CPanel Deployment Script
# ======================================
#
# This script handles backend deployment on cPanel.
# Update DEPLOY_DIR to match your cPanel subdomain path.
#

# Exit on any error
set -e

echo "🚀 Starting BEKASEMWEB Backend Deployment..."

# Navigate to deployment directory
# UPDATE THIS: Replace 'username' with your actual cPanel username
DEPLOY_DIR="/home/username/api.yazidtest.my.id"
cd $DEPLOY_DIR

# Copy production env if .env doesn't exist
if [ ! -f .env ]; then
    echo "📋 Setting up .env from .env.production..."
    cp .env.production .env
    echo "⚠️  Remember to update DB credentials in .env!"
fi

# Install composer dependencies (production)
echo "📦 Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Generate app key if not set
php artisan key:generate --force

# Run migrations
echo "🔄 Running database migrations..."
php artisan migrate --force

# Create storage symlink
echo "🔗 Creating storage symlink..."
php artisan storage:link --force

# Clear and optimize
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
echo "🔐 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/logs
chmod -R 775 storage/app/public

echo "✅ Deployment completed successfully!"
