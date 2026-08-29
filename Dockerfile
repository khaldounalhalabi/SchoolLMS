# ============================
# Stage 1: Build assets
# ============================
FROM node:22-alpine AS node-builder
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm install
COPY . .
RUN npm run build

# ============================
# Stage 2: PHP-FPM + nginx runtime
# ============================
FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    su-exec \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    oniguruma-dev \
    libxml2-dev \
    mariadb-client \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    opcache \
    xml

COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

COPY --from=node-builder /app/public/build ./public/build
COPY . .

COPY docker/nginx.conf /etc/nginx/http.d/default.conf

RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/logs \
    bootstrap/cache \
    /run/nginx \
    && chown -R www-data:www-data /var/www \
    && chown -R nginx:nginx /run/nginx /var/lib/nginx /var/log/nginx

COPY --chown=www-data:www-data docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80

HEALTHCHECK \
    --interval=30s \
    --timeout=5s \
    --start-period=30s \
    --retries=3 \
    CMD curl -fsS http://localhost:80/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["nginx-php-fpm"]
