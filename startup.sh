#!/bin/bash

# Patch the Oryx nginx config to serve from Laravel's public directory
for conf in /tmp/oryx/images/runtime/php-fpm/nginx_conf/default.conf \
            /etc/nginx/sites-available/default \
            /etc/nginx/sites-enabled/default \
            /etc/nginx/conf.d/default.conf; do
    if [ -f "$conf" ]; then
        sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/public;|g' "$conf"
        sed -i 's|root /home/site/wwwroot |root /home/site/wwwroot/public |g' "$conf"
    fi
done

# Fix permissions
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true

# Create SQLite DB if missing
touch /home/site/wwwroot/database/database.sqlite

# Run migrations
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true

# Reload nginx
nginx -s reload 2>/dev/null || true
