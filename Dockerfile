# --- Stage 1: PHP Dependencies ---
FROM php:8.3-fpm-alpine as vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN apk add --no-cache git unzip libxml2-dev libpng-dev libzip-dev \
    && docker-php-ext-install bcmath gd zip \
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
RUN apk add --no-cache nginx supervisor libpng-dev libzip-dev \
    && docker-php-ext-install gd zip pdo_mysql bcmath \
    && mkdir -p /run/nginx

# Copy PHP dependencies from Stage 1
COPY --from=vendor /app/vendor ./vendor

# Copy Application code
COPY . .

# Copy Frontend assets from Stage 2 (AFTER app code to ensure they're not overwritten)
COPY --from=frontend /app/public/build ./public/build

# Set permissions - ensure all files are readable
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public \
    && chmod -R 755 /var/www/html/public \
    && chmod -R 644 /var/www/html/public/*

# Custom Nginx Config
COPY <<EOF /etc/nginx/http.d/default.conf
server {
    listen 80;
    server_name _;
    root /var/www/html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_param HTTPS \$https;
        fastcgi_param HTTP_X_FORWARDED_FOR \$proxy_add_x_forwarded_for;
        fastcgi_param HTTP_X_FORWARDED_PROTO \$scheme;
        fastcgi_param HTTP_X_FORWARDED_HOST \$server_name;
        fastcgi_param PHP_VALUE "display_errors=1";
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Custom Supervisor Config to run both Nginx and PHP-FPM
COPY <<EOF /etc/supervisor/conf.d/supervisord.conf
[supervisord]
nodaemon=true
user=root
logfile=/var/log/supervisord.log
pidfile=/run/supervisord.pid

[program:php-fpm]
command=php-fpm
stdout_logfile=/dev/stdout
stderr_logfile=/dev/stderr

[program:nginx]
command=nginx -g "daemon off;"
stdout_logfile=/dev/stdout
stderr_logfile=/dev/stderr
EOF

# Expose port 80
EXPOSE 80

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
