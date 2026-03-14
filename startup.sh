#!/bin/bash

# Overwrite the Oryx nginx config entirely with correct document root
NGINX_CONF="/tmp/oryx/images/runtime/php-fpm/nginx_conf/default.conf"

cat > "$NGINX_CONF" << 'EOF'
server {
    listen 8080;
    listen [::]:8080;

    root /home/site/wwwroot/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param SCRIPT_NAME $fastcgi_script_name;
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

# Fix permissions
chmod -R 775 /home/site/wwwroot/storage /home/site/wwwroot/bootstrap/cache 2>/dev/null || true

# Create SQLite DB if missing
touch /home/site/wwwroot/database/database.sqlite

# Run migrations
cd /home/site/wwwroot && php artisan migrate --force 2>/dev/null || true

# Reload nginx with new config
nginx -s reload
