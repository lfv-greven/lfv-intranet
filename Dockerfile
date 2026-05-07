# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm-bookworm AS php-base
WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
    ca-certificates \
    curl \
    gettext-base \
    libcurl4-openssl-dev \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    nginx \
    supervisor \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        curl \
        dom \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM php-base AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

FROM node:26-alpine AS assets
WORKDIR /app

COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci

COPY --from=vendor /app/vendor /app/vendor

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
RUN npm run build

FROM php-base AS runtime

ENV APP_ENV=production \
    PORT=8080 \
    ENABLE_QUEUE_WORKER=true

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

COPY docker/php.ini /usr/local/etc/php/conf.d/99-app.ini
COPY docker/php-fpm-www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY docker/nginx.conf /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf /etc/supervisor/supervisord.conf
COPY docker/supervisor /etc/supervisor/conf.d
COPY docker/entrypoint.sh /usr/local/bin/entrypoint

RUN rm -f bootstrap/cache/*.php \
    && php artisan package:discover --ansi \
    && php artisan optimize:clear --ansi \
    && rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default \
    && chmod +x /usr/local/bin/entrypoint \
    && mkdir -p /run/php /var/log/supervisor \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

HEALTHCHECK --interval=30s --timeout=5s --start-period=30s --retries=3 \
  CMD curl -fsS "http://127.0.0.1:${PORT}/up" || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint"]
