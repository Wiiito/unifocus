# syntax=docker/dockerfile:1
###############################################################################
# Unifocus — imagem PHP 8.4 (FPM) para o projeto Laravel 13
###############################################################################
FROM php:8.4-fpm-alpine

# UID/GID do dono do código no host, para que os arquivos gerados dentro do
# container (artisan make, composer, npm) não fiquem como root no bind mount.
ARG UID=1000
ARG GID=1000

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache \
        bash \
        git \
        curl \
        unzip \
        icu-data-full \
        postgresql-client \
        nodejs \
        npm \
    && install-php-extensions \
        pdo_pgsql \
        pgsql \
        intl \
        zip \
        bcmath \
        gd \
        exif \
        pcntl \
        sockets \
        opcache \
    && rm -rf /var/cache/apk/*

# Usuário da aplicação espelhando o UID/GID do host.
RUN addgroup -g ${GID} app \
    && adduser -u ${UID} -G app -s /bin/bash -D app \
    && mkdir -p /var/www/html \
    && chown -R app:app /var/www/html

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-unifocus.ini
COPY docker/php/www-pool.conf /usr/local/etc/php-fpm.d/zzz-unifocus.conf
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

WORKDIR /var/www/html
USER app

EXPOSE 9000
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm", "-F"]
