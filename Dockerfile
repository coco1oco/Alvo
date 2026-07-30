# ==========================================
# Stage 1: Build Frontend Assets (Vite / Vue)
# ==========================================
FROM node:20-alpine AS frontend-builder

WORKDIR /app

COPY package*.json ./
RUN npm ci

COPY . .
RUN npm run build

# ==========================================
# Stage 2: Install Production Composer Dependencies
# ==========================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --ignore-platform-reqs --no-scripts

# ==========================================
# Stage 3: Production Runtime (PHP 8.3-FPM + Nginx)
# ==========================================
FROM php:8.3-fpm-alpine

# Install Nginx, Supervisor, and required system libraries
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpng-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    freetype-dev \
    jpeg-dev \
    libjpeg-turbo-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        bcmath \
        intl \
        zip \
        opcache \
        gd

WORKDIR /var/www/html

# Copy application files from context and build stages
COPY . .
COPY --from=composer-builder /app/vendor /var/www/html/vendor
COPY --from=frontend-builder /app/public/build /var/www/html/public/build

# Production Nginx Configuration
RUN mkdir -p /etc/nginx/http.d
RUN echo 'server { \
    listen 80; \
    server_name _; \
    root /var/www/html/public; \
    index index.php index.html; \
    charset utf-8; \
    client_max_body_size 20M; \
    location / { \
        try_files $uri $uri/ /index.php?$query_string; \
    } \
    location = /favicon.ico { access_log off; log_not_found off; } \
    location = /robots.txt  { access_log off; log_not_found off; } \
    error_page 404 /index.php; \
    location ~ \.php$ { \
        fastcgi_pass 127.0.0.1:9000; \
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name; \
        include fastcgi_params; \
    } \
    location ~ /\.(?!well-known).* { \
        deny all; \
    } \
}' > /etc/nginx/http.d/default.conf

# Production Supervisor Configuration
RUN mkdir -p /etc/supervisor/conf.d
RUN echo '[supervisord] \
nodaemon=true \
logfile=/var/log/supervisord.log \
pidfile=/var/run/supervisord.pid \
\
[program:php-fpm] \
command=docker-php-entrypoint php-fpm \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0 \
autorestart=true \
\
[program:nginx] \
command=nginx -g "daemon off;" \
stdout_logfile=/dev/stdout \
stdout_logfile_maxbytes=0 \
stderr_logfile=/dev/stderr \
stderr_logfile_maxbytes=0 \
autorestart=true' > /etc/supervisor/conf.d/supervisord.conf

# Set permissions for storage & bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
