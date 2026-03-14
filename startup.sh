#!/bin/bash
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true
touch /home/site/wwwroot/database/database.sqlite
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true
