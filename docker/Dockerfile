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
# Use Debian-slim base instead of Alpine — mature apt packages for PHP
# extensions, no musl-compilation surprises, widely used for Laravel.
FROM php:8.3-fpm-bookworm AS runtime

# mlocati/docker-php-extension-installer — battle-tested helper that resolves
# apt-get dev deps for each extension and cleans them up afterwards.
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update \
    && apt-get install -y --no-install-recommends nginx supervisor tzdata curl git ca-certificates \
    && install-php-extensions \
        pdo_sqlite \
        intl \
        zip \
        bcmath \
        pcntl \
        gd \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

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

# Ensure storage + bootstrap/cache are writable (even though volume-mounted in compose).
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
             storage/framework/testing storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Finalize composer autoload with the full app tree in place.
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative \
    && rm -rf /root/.composer /usr/local/bin/composer

# Entrypoint: run migrations + cache + storage:link before starting processes
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]
