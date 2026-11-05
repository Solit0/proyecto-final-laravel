FROM composer:2.10.3 AS composer_binary

FROM php:8.3.35-cli-alpine3.24

RUN apk add --no-cache git unzip icu-libs libpq libzip libpng libjpeg-turbo freetype \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libpq-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j2 pdo_pgsql intl zip gd \
    && apk del .build-deps

COPY --from=composer_binary /usr/bin/composer /usr/local/bin/composer

ENV COMPOSER_HOME=/tmp/composer \
    XDG_CONFIG_HOME=/tmp/xdg

WORKDIR /app

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-interaction"]
