# syntax=docker/dockerfile:1
#
# Image aplikasi: PHP 8.5 (cli, alpine) + Node 22 + Composer + ekstensi PHP.
# Dipakai oleh service `app` di docker-compose.yml.
#
# Catatan penting:
#   * Node 22 diambil dari image resmi `node:22-alpine` supaya versinya benar-benar 22
#     (repo alpine 3.24 sudah menyediakan Node 24). Base `php:8.5-cli-alpine` dan
#     `node:22-alpine` sama-sama alpine 3.24, jadi binary Node bisa disalin apa adanya.
#   * `public/build` dihasilkan di sini (npm run build) supaya aplikasi tetap tampil
#     rapi walau Vite (MULAI_VITE) tidak dinyalakan.

# --- Node 22 (dipakai untuk Vite/Tailwind) --------------------------------
FROM node:22-alpine AS node

# --- Image aplikasi ------------------------------------------------------
FROM php:8.5-cli-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

# Node & npm dari image resmi.
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
 && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx \
 && node -v \
 && npm -v

# Composer.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Paket sistem + ekstensi PHP yang dibutuhkan Laravel/Tailwind.
# (pdo_sqlite, mbstring, opcache sudah ada di image dasar — dipakai `php artisan test`.)
RUN apk add --no-cache \
        bash \
        git \
        curl \
        unzip \
        libzip-dev \
        icu-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        bcmath \
        intl \
        zip \
        pcntl \
        exif \
    && apk del --no-cache .build-deps \
    && rm -rf /var/cache/apk/*

# Setelan php.ini khusus aplikasi.
COPY docker/php/php.ini $PHP_INI_DIR/conf.d/99-laravel.ini

WORKDIR /var/www/html

# Kode proyek + dependensi (vendor/ dan node_modules/ tidak ikut git, jadi
# dipasang di dalam image). public/build juga dibuat di sini.
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress \
    && npm ci --no-audit --no-fund \
    && npm run build \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/php/entrypoint.sh

EXPOSE 8000

HEALTHCHECK --interval=15s --timeout=5s --start-period=40s --retries=5 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1:8000/up") === false ? 1 : 0);'

ENTRYPOINT ["/var/www/html/docker/php/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
