#!/bin/bash

# Stop nginx, override the document root env var Oryx sets, restart
service nginx stop

export NGINX_DOCUMENT_ROOT='/home/site/wwwroot/public'
service nginx start

# Fix permissions
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true

# Create SQLite DB if missing
touch /home/site/wwwroot/database/database.sqlite

# Run migrations
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true
