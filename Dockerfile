# syntax=docker/dockerfile:1
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

COPY .github/data/php/php.ini           /usr/local/etc/php/conf.d/magento.ini
COPY .github/data/php/opcache.ini       /usr/local/etc/php/conf.d/opcache.ini
COPY .github/data/nginx/nginx.conf      /etc/nginx/nginx.conf
COPY .github/data/nginx/default.conf    /etc/nginx/conf.d/default.conf
COPY .github/data/supervisor/supervisord.conf /etc/supervisord.conf
COPY .github/data/entrypoint/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

WORKDIR /var/www/html

# ────────────────────────────────────────────
FROM base AS build

# Install Magento — this layer is cached until the version changes
RUN --mount=type=secret,id=composer_auth,src=./auth.json,dst=/root/.composer/auth.json \
    composer create-project \
        --repository-url=https://repo.magento.com/ \
        magento/project-community-edition:2.4.* . \
        --no-interaction \
        --no-progress

# Copy module source and require it — only this layer reruns on code changes
COPY . /tmp/module
RUN composer config repositories.local \
        '{"type":"path","url":"/tmp/module","options":{"symlink":false}}' \
    && composer require pagarme/pagarme-magento2-module:* \
        --no-interaction \
        --no-progress

# ────────────────────────────────────────────
FROM base AS production

COPY --from=build /var/www/html /var/www/html

RUN find var generated vendor pub/static pub/media app/etc -type f -exec chmod g+w {} + 2>/dev/null || true \
    && find var generated vendor pub/static pub/media app/etc -type d -exec chmod g+ws {} + 2>/dev/null || true \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=10s --start-period=120s --retries=3 \
    CMD curl -sf http://localhost/health_check.php || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
