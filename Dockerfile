# Multi-stage production image for Laravel + Vite

FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm ci

COPY vite.config.js ./
COPY resources/ resources/

RUN npm run build

FROM php:8.4-cli-alpine AS vendor

RUN apk add --no-cache \
    git \
    curl \
    curl-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        bcmath \
        curl \
        dom \
        exif \
        gd \
        intl \
        mbstring \
        pdo_mysql \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
# NOT: --prefer-dist KASITLI olarak kaldırıldı. Strict dist iken bir dist zip'i
# (codeload.github.com) geçici HTTP 400/5xx verince composer source fallback'i
# kapatıp ("Source fallback is disabled") tüm build'i düşürüyordu. preferred-install
# artık composer.json'da "auto" → dist başarısız olursa git'ten klonlar (vendor
# aşamasında git kurulu). Böylece tek geçici indirme hatası build'i bozmaz.
RUN composer install \
    --no-dev \
    --no-interaction \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-req=ext-gd \
    --ignore-platform-req=ext-exif


COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    curl-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libwebp-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    libxml2-dev \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        curl \
        dom \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        simplexml \
        xml \
        xmlreader \
        xmlwriter \
        zip \
        intl \
        opcache \
    && rm -rf /var/cache/apk/*

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-production.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf

COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

WORKDIR /var/www/html

COPY --from=vendor /app .
COPY --from=frontend /app/public/build public/build

RUN mkdir -p \
    storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN php artisan storage:link 2>/dev/null || true

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
