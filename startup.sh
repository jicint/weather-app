#!/bin/bash

echo "=== Oryx startup.sh ==="
cat /opt/startup/startup.sh

echo "=== nginx master pid ==="
cat /tmp/nginx.pid 2>/dev/null || cat /var/run/nginx.pid 2>/dev/null || pgrep nginx | head -1

echo "=== nginx -T (active config) ==="
nginx -T 2>&1 | grep -A2 "root"

# Fix permissions
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true
touch /home/site/wwwroot/database/database.sqlite
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true
