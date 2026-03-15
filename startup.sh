#!/bin/bash

cd /home/site/wwwroot

# Generate .env from Azure app settings
cat > .env << EOF
APP_NAME="${APP_NAME:-Weather Checker}"
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-https://weather-checker-app.azurewebsites.net}

DB_CONNECTION=${DB_CONNECTION:-sqlite}
DB_DATABASE=${DB_DATABASE:-/home/site/wwwroot/database/database.sqlite}

SESSION_DRIVER=${SESSION_DRIVER:-file}
CACHE_DRIVER=${CACHE_DRIVER:-file}
LOG_CHANNEL=${LOG_CHANNEL:-stderr}

OPENWEATHER_API_KEY=${OPENWEATHER_API_KEY}
EOF

# Fix permissions
chmod -R 775 storage bootstrap/cache

# Create SQLite DB if missing
touch database/database.sqlite

# Cache config so PHP-FPM reads from file (bypasses clear_env issue)
php artisan config:cache
php artisan route:cache

# Run migrations
php artisan migrate --force
