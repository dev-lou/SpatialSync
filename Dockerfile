# --- Stage 1: PHP Dependencies ---
FROM php:8.3-fpm-alpine as vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN apk add --no-cache git unzip libxml2-dev libpng-dev libzip-dev libpq-dev \
    && docker-php-ext-install bcmath gd zip pdo pdo_pgsql pgsql \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# --- Stage 2: Frontend Assets ---
FROM node:20-alpine as frontend
WORKDIR /app
COPY . .
RUN npm install && npm run build

# --- Stage 3: Final Production Image ---
FROM php:8.3-fpm-alpine
WORKDIR /var/www/html

# Install system dependencies & Nginx
RUN apk add --no-cache nginx supervisor libpng-dev libzip-dev libpq-dev \
    && docker-php-ext-install gd zip pdo pdo_pgsql pgsql bcmath \
    && sed -i 's/^user\s\+nginx;/user www-data;/' /etc/nginx/nginx.conf \
    && mkdir -p /run/nginx

# Copy PHP dependencies from Stage 1
COPY --from=vendor /app/vendor ./vendor

# Copy Application code
COPY . .

# Copy Frontend assets from Stage 2 (AFTER app code to ensure they're not overwritten)
COPY --from=frontend /app/public/build ./public/build

# Set permissions - ensure all files are readable
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public \
    && chmod -R a+rX /var/www/html/public

# Custom Nginx Config
COPY <<'EOF' /etc/nginx/http.d/default.conf
server {
    listen 80;
    server_name _;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~* \.(?:css|js|mjs|map|jpg|jpeg|gif|png|svg|webp|ico|ttf|woff|woff2)$ {
        access_log off;
        expires 30d;
        add_header Cache-Control "public, max-age=2592000, immutable";
        try_files $uri =404;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param HTTPS on;
        fastcgi_param HTTP_X_FORWARDED_FOR $proxy_add_x_forwarded_for;
        fastcgi_param HTTP_X_FORWARDED_PROTO $http_x_forwarded_proto;
        fastcgi_param HTTP_X_FORWARDED_HOST $http_host;
        fastcgi_param HTTP_X_FORWARDED_PORT $http_x_forwarded_port;
        fastcgi_param PHP_VALUE "display_errors=1";
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Custom Supervisor Config to run both Nginx and PHP-FPM
COPY <<'EOF' /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/dev/null
logfile_maxbytes=0
pidfile=/run/supervisord.pid

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true
priority=10
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
priority=20
stdout_logfile=/dev/stdout
stdout_logfile_maxbytes=0
stderr_logfile=/dev/stderr
stderr_logfile_maxbytes=0
EOF

# Startup script to prepare writable runtime paths on mounted volumes
COPY <<'EOF' /usr/local/bin/start-container
#!/bin/sh
set -e

mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/storage/logs /var/www/html/bootstrap/cache

if [ "$(id -u)" -eq 0 ]; then
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
fi

chmod -R ug+rwX /var/www/html/storage /var/www/html/bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is not set. Configure APP_KEY in Render environment variables."
    exit 1
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
EOF

RUN chmod +x /usr/local/bin/start-container

# Expose port 80
EXPOSE 80

# Start container entrypoint
CMD ["/usr/local/bin/start-container"]
