#!/bin/bash

# Generate .env from Azure app settings if it doesn't exist
cat > /home/site/wwwroot/.env << EOF
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
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true

# Create SQLite DB if missing
touch /home/site/wwwroot/database/database.sqlite

# Run migrations
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true
