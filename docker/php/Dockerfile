# syntax=docker/dockerfile:1
#
# Image aplikasi: PHP 8.5 (cli, alpine) + Node 22 + Composer + ekstensi PHP.
# Dipakai oleh service `app` di docker-compose.yml.
#
# Catatan penting:
#   * Node 22 diambil dari image resmi `node:22-alpine` supaya versinya benar-benar 22
#     (repo alpine 3.24 sudah menyediakan Node 24). Base `php:8.5-cli-alpine` dan
#     `node:22-alpine` sama-sama alpine 3.24, jadi binary Node bisa disalin apa adanya —
#     TAPI binary Node butuh `libstdc++` yang tidak ada di image PHP, jadi paket itu
#     harus dipasang LEBIH DULU (sebelum `node` dijalankan sekali pun).
#   * `public/build` dihasilkan di sini (npm run build) supaya aplikasi tetap tampil
#     rapi walau Vite (MULAI_VITE) tidak dinyalakan.

# --- Node 22 (dipakai untuk Vite/Tailwind) --------------------------------
FROM node:22-alpine AS node

# --- Image aplikasi ------------------------------------------------------
FROM php:8.5-cli-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer

# 1. Paket sistem + ekstensi PHP yang dibutuhkan Laravel/Tailwind.
#    (pdo_sqlite, mbstring, opcache sudah ada di image dasar — dipakai `php artisan test`.)
RUN apk add --no-cache \
        bash \
        git \
        curl \
        unzip \
        libstdc++ \
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
    && apk del .build-deps \
    && rm -rf /var/cache/apk/*

# 2. Node 22 + npm. Hanya disalin & ditautkan di sini; `node` baru dijalankan
#    pada langkah 4 setelah libstdc++ tersedia.
COPY --from=node /usr/local/bin/node /usr/local/bin/node
COPY --from=node /usr/local/lib/node_modules/npm /usr/local/lib/node_modules/npm
RUN ln -sf /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm \
 && ln -sf /usr/local/lib/node_modules/npm/bin/npx-cli.js /usr/local/bin/npx

# 3. Composer.
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# 4. Buktikan toolchain hidup (gagal di sini jauh lebih jelas daripada nanti).
RUN node -v && npm -v && composer --version && php -m | grep -E '^(pdo_mysql|pdo_sqlite|intl|zip|bcmath|pcntl|exif)$'

# 5. Setelan php.ini khusus aplikasi.
COPY docker/php/php.ini $PHP_INI_DIR/conf.d/99-laravel.ini

WORKDIR /var/www/html

# 6. Kode proyek + dependensi (vendor/ dan node_modules/ tidak ikut git, jadi
#    dipasang di dalam image). public/build juga dibuat di sini.
COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress \
    && npm ci --no-audit --no-fund \
    && npm run build \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/php/entrypoint.sh

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1:8000/up") === false ? 1 : 0);'

ENTRYPOINT ["/var/www/html/docker/php/entrypoint.sh"]
# --no-reload: tanpa flag ini `php artisan serve` mengabaikan PHP_CLI_SERVER_WORKERS
# (lihat docker-compose.yml), sehingga server hanya melayani satu permintaan sekaligus.
# Flag ini HANYA mematikan pemuatan ulang otomatis saat .env berubah — perubahan kode
# tetap langsung terbaca tiap permintaan.
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000", "--no-reload"]
