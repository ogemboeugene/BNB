#!/bin/bash

# Azure startup script for Laravel
echo "Starting Laravel application setup..."

# Create storage directories if they don't exist
mkdir -p /home/site/wwwroot/storage/logs
mkdir -p /home/site/wwwroot/storage/framework/cache
mkdir -p /home/site/wwwroot/storage/framework/sessions
mkdir -p /home/site/wwwroot/storage/framework/views

# Set proper permissions
chmod -R 755 /home/site/wwwroot/storage
chmod -R 755 /home/site/wwwroot/bootstrap/cache

# Run Laravel setup commands
php artisan config:cache
php artisan route:cache
php artisan storage:link

echo "Laravel application setup completed."