# syntax=docker/dockerfile:1.6
# ─── Stage 1: composer deps ──────────────────────────────────────────
FROM composer:2 AS composer-deps
WORKDIR /app
COPY composer.json composer.lock ./
# --no-scripts so artisan package:discover (which needs Laravel autoloader) doesn't run here.
RUN composer install --no-dev --no-interaction --no-scripts --no-autoloader --prefer-dist

# ─── Stage 2: JS build (vite) ────────────────────────────────────────
FROM node:20-alpine AS node-assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js tsconfig.json ./
COPY public ./public
RUN npm run build

# ─── Stage 3: runtime (php-fpm) ──────────────────────────────────────
FROM php:8.3-fpm-alpine AS runtime

# System deps + PHP extensions Laravel needs (sqlite, intl, zip, gd, bcmath, pcntl, opcache)
RUN apk add --no-cache \
        git \
        curl \
        nginx \
        supervisor \
        tzdata \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_sqlite \
        intl \
        zip \
        mbstring \
        bcmath \
        pcntl \
        gd \
        opcache \
    && apk del icu-dev libzip-dev oniguruma-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    && rm -rf /var/cache/apk/*

# Pre-tuned opcache + php-fpm pool for prod
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

# Internal nginx (serves public/, proxies to php-fpm)
COPY docker/nginx-container.conf /etc/nginx/nginx.conf

# Supervisor runs php-fpm + nginx together
COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /app

# App code
COPY --chown=www-data:www-data . /app

# Composer deps from stage 1
COPY --from=composer-deps --chown=www-data:www-data /app/vendor /app/vendor

# Vite build artifacts from stage 2
COPY --from=node-assets --chown=www-data:www-data /app/public/build /app/public/build

# Ensure storage + bootstrap/cache are writable (even though volume-mounted in compose)
RUN mkdir -p storage/framework/{cache,sessions,testing,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Finalize composer autoload with app code in place
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && rm -rf /root/.composer

# Entrypoint: run migrations + cache + storage:link before starting processes
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
