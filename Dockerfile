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
# Stage 2: PHP + FrankenPHP runtime
# ============================
# Pin a specific version, not just "latest", for reproducible builds.
# Check https://hub.docker.com/r/dunglas/frankenphp/tags for the current stable tag.
FROM dunglas/frankenphp:1.7.0-php8.4-alpine

# Install system deps and PHP extensions
# NOTE: this frankenphp base doesn't ship "nginx" or "supervisor" — FrankenPHP
# (built on Caddy) is the server itself, so those packages have been dropped.
RUN apk add --no-cache \
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

# Install Composer (pin to a specific major.minor if you want stricter reproducibility)
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# Install and enable the Redis PHP extension (used for cache/sessions/queues)
RUN apk add --no-cache --virtual .build-deps autoconf g++ make su-exec \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www

# Copy composer files first for layer caching
COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-interaction --no-scripts

# Copy built assets from node stage
COPY --from=node-builder /app/public/build ./public/build

# Copy the rest of the app
COPY . .

# Ensure runtime directories exist and are writable
RUN mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www

# ----------------------------
# Entrypoint
# ----------------------------
COPY --chown=www-data:www-data \
    docker/entrypoint.sh \
    /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
# no USER line here — container starts as root so the entrypoint
# can chown the mounted volumes, then drops privileges to www-data itself

# ----------------------------
# FrankenPHP
# ----------------------------
EXPOSE 80
HEALTHCHECK \
    --interval=30s \
    --timeout=5s \
    --start-period=30s \
    --retries=3 \
    CMD curl -fsS http://localhost:80/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["php", "artisan", "octane:frankenphp", "--port=80"]
