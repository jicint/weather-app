#!/bin/bash

# Point nginx at Laravel's public directory
sed -i 's|/home/site/wwwroot|/home/site/wwwroot/public|g' /etc/nginx/sites-available/default

# Fix permissions
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true

# Create SQLite DB if missing
touch /home/site/wwwroot/database/database.sqlite

# Run migrations
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true

# Reload nginx
nginx -s reload 2>/dev/null || service nginx reload 2>/dev/null || true
