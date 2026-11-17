# syntax=docker/dockerfile:1
###############################################################################
# Unifocus — imagem de PRODUÇÃO (Render).
# Um único container roda nginx + PHP-FPM + fila + agendador via supervisord,
# porque o plano free do Render oferece um só serviço com 512 MB.
# O Dockerfile da raiz continua sendo o de desenvolvimento (docker compose).
###############################################################################

# --- 1. Dependências PHP ------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist --ignore-platform-reqs

# --- 2. Assets do front (Vite) -----------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts
COPY . .
# O Tailwind escaneia as views de paginação dentro de vendor/.
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# --- 3. Runtime ---------------------------------------------------------------
FROM php:8.4-fpm-alpine

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apk add --no-cache bash nginx supervisor icu-data-full postgresql-client \
    && install-php-extensions pdo_pgsql pgsql intl zip bcmath gd exif pcntl sockets opcache \
    && rm -rf /var/cache/apk/*

COPY docker/prod/php.ini /usr/local/etc/php/conf.d/99-unifocus.ini
COPY docker/prod/www-pool.conf /usr/local/etc/php-fpm.d/zzz-unifocus.conf
COPY docker/prod/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/prod/supervisord.conf /etc/supervisord.conf
COPY docker/prod/start.sh /usr/local/bin/start
RUN chmod +x /usr/local/bin/start

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

ENV PORT=10000
EXPOSE 10000

CMD ["start"]
