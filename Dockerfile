FROM php:8.2-fpm-alpine AS base

RUN apk add --no-cache \
    bash \
    coreutils \
    curl \
    freetype-dev \
    gettext \
    git \
    icu-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libwebp-dev \
    libxml2-dev \
    libxslt-dev \
    libzip-dev \
    linux-headers \
    mysql-client \
    nginx \
    oniguruma-dev \
    openssh-client \
    patch \
    shadow \
    supervisor \
    unzip \
    zip \
    zlib-dev

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

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini           /usr/local/etc/php/conf.d/magento.ini
COPY docker/php/opcache.ini       /usr/local/etc/php/conf.d/opcache.ini
COPY docker/nginx/nginx.conf      /etc/nginx/nginx.conf
COPY docker/nginx/default.conf    /etc/nginx/conf.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

# ────────────────────────────────────────────
FROM base AS build

# Copy composer auth for repo.magento.com
COPY auth.json /root/.composer/auth.json

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-progress

COPY . .

# Generate config.php and compile
RUN php bin/magento module:enable --all \
    && php bin/magento setup:di:compile \
    && php bin/magento setup:static-content:deploy -f en_US

# ────────────────────────────────────────────
FROM base AS production

COPY --from=build /var/www/html /var/www/html

RUN find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} + 2>/dev/null || true \
    && find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} + 2>/dev/null || true \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
