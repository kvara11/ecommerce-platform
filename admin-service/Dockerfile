FROM php:8.2-fpm-alpine

WORKDIR /var/www/admin-service

RUN apk add --no-cache \
    bash git zip unzip \
    curl \
    icu-dev oniguruma-dev libzip-dev \
    libxml2-dev \
    postgresql-dev \
    curl-dev \
    openssl-dev \
    pkgconf \
    $PHPIZE_DEPS

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    intl \
    zip \
    opcache \
    dom \
    xml \
    xmlwriter \
    curl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN mkdir -p storage bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]