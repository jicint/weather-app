#!/bin/bash

# Set Laravel public dir as nginx root
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default

# Ensure storage and bootstrap/cache are writable
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache

# Create SQLite database if it doesn't exist
touch /home/site/wwwroot/database/database.sqlite

# Run migrations
cd /home/site/wwwroot && php artisan migrate --force

# Reload nginx
service nginx reload
