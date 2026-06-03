FROM php:8.3-fpm

RUN apt-get update && apt-get install -y --no-install-recommends \
    autoconf \
    bash \
    coreutils \
    curl \
    default-mysql-client \
    g++ \
    gettext \
    git \
    libfreetype6-dev \
    libicu-dev \
    libjpeg62-turbo-dev \
    libonig-dev \
    libpng-dev \
    libssl-dev \
    libwebp-dev \
    libxml2-dev \
    libxslt1-dev \
    libzip-dev \
    m4 \
    make \
    openssh-client \
    patch \
    unzip \
    zip \
    zlib1g-dev \
 && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        ftp \
        gd \
        intl \
        mbstring \
        mysqli \
        opcache \
        pdo_mysql \
        soap \
        sockets \
        xsl \
        zip

RUN pecl install redis xdebug \
 && docker-php-ext-enable redis xdebug

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY php.ini     /usr/local/etc/php/conf.d/magento.ini
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY xdebug.ini  /usr/local/etc/php/conf.d/xdebug.ini

WORKDIR /var/www/html
