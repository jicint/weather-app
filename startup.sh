#!/bin/bash

# Find and log all nginx config files
echo "=== Searching for nginx configs ==="
find / -path /proc -prune -o -path /sys -prune -o -name "*.conf" -print 2>/dev/null | xargs grep -l "wwwroot" 2>/dev/null

# Patch document root in all found configs
for conf in /etc/nginx/sites-available/default \
            /etc/nginx/sites-enabled/default \
            /etc/nginx/conf.d/default.conf \
            /opt/startup/startup-nginx.conf \
            /opt/startup/nginx-default.conf; do
    if [ -f "$conf" ]; then
        echo "=== Patching $conf ==="
        sed -i 's|root /home/site/wwwroot;|root /home/site/wwwroot/public;|g' "$conf"
        sed -i 's|root /home/site/wwwroot |root /home/site/wwwroot/public |g' "$conf"
        grep "root " "$conf"
    fi
done

# Fix permissions
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true

# Create SQLite DB if missing
touch /home/site/wwwroot/database/database.sqlite

# Run migrations
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true

# Reload nginx
echo "=== Reloading nginx ==="
nginx -s reload 2>/dev/null || true
