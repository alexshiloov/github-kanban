FROM php:7.2-fpm-alpine

RUN apk upgrade --update && apk add --no-cache \
    $PHPIZE_DEPS \
    git \
    wget \
    supervisor \
    icu-dev \
    libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    openssl-dev \
    pkgconfig \
    unixodbc-dev \
    zlib-dev libzip-dev \
    tzdata

RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime && "date"

RUN docker-php-ext-configure zip --with-libzip \
    && docker-php-ext-install -j$(nproc) iconv pdo opcache mbstring zip pcntl \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ --with-png-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

RUN pecl install apcu \
    && docker-php-ext-enable apcu

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version


WORKDIR /var/www/html