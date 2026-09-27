#!/bin/sh
#
# entrypoint.sh — disiapkan supaya "clone -> docker compose up -d -> jalan".
# Urutannya sama di ketiga template (blade / livewire / react).
#
#   1. .env dibuat dari variabel lingkungan wadah (BUKAN `cp .env.example`, karena
#      .env.example memuat DB_HOST=127.0.0.1 -> aplikasi akan menyambung ke dirinya sendiri)
#   2. tunggu MySQL benar-benar siap (fsockopen, maks ~120 detik)
#   3. `php artisan key:generate` kalau APP_KEY masih kosong
#   4. `php artisan config:clear route:clear view:clear`
#   5. JALANKAN_MIGRASI=true -> `php artisan migrate --force`
#   6. TANAM_DATA=true       -> `php artisan db:seed --force` (idempoten)
#   7. APP_ENV=production    -> `php artisan config:cache route:cache view:cache`
#   8. MULAI_VITE=true       -> `npm run dev` di latar belakang
#   9. `exec "$@"`           -> perintah utama container
#
set -eu

cd /var/www/html

log() {
    printf '[entrypoint] %s\n' "$*"
}

gagal() {
    printf '[entrypoint] GAGAL: %s\n' "$*" >&2
    exit 1
}

# ---------------------------------------------------------------------------
# 1. .env
# ---------------------------------------------------------------------------
if [ ! -f .env ]; then
    log "Menulis .env dari variabel lingkungan wadah ..."
    cat > .env <<EOF
APP_NAME="${APP_NAME:-Laravel Blade Docker}"
APP_ENV=${APP_ENV:-local}
APP_DEBUG=${APP_DEBUG:-true}
APP_TIMEZONE=${APP_TIMEZONE:-Asia/Jakarta}
APP_URL=${APP_URL:-http://localhost:8300}
APP_LOCALE=${APP_LOCALE:-id}
APP_FALLBACK_LOCALE=${APP_FALLBACK_LOCALE:-en}
APP_FAKER_LOCALE=${APP_FAKER_LOCALE:-id_ID}
APP_KEY=

DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-db}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-laravel}
DB_USERNAME=${DB_USERNAME:-laravel}
DB_PASSWORD=${DB_PASSWORD:-}

SESSION_DRIVER=${SESSION_DRIVER:-database}
SESSION_LIFETIME=${SESSION_LIFETIME:-120}
CACHE_STORE=${CACHE_STORE:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}

LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-debug}
MAIL_MAILER=${MAIL_MAILER:-log}
EOF
else
    log ".env sudah ada — dipakai apa adanya."
fi

# ---------------------------------------------------------------------------
# 2. Tunggu MySQL
# ---------------------------------------------------------------------------
if [ "${DB_CONNECTION:-mysql}" = "mysql" ] || [ "${DB_CONNECTION:-mysql}" = "mariadb" ]; then
    DB_HOST_AKTIF="${DB_HOST:-db}"
    DB_PORT_AKTIF="${DB_PORT:-3306}"
    BATAS="${TUNGGU_DB_DETIK:-120}"

    log "Menunggu MySQL di ${DB_HOST_AKTIF}:${DB_PORT_AKTIF} (maks ${BATAS} detik) ..."
    sudah=0
    while ! php -r 'exit(@fsockopen($argv[1], (int) $argv[2], $kode, $pesan, 2) ? 0 : 1);' "$DB_HOST_AKTIF" "$DB_PORT_AKTIF" >/dev/null 2>&1; do
        sudah=$((sudah + 1))
        if [ "$sudah" -ge "$BATAS" ]; then
            gagal "MySQL di ${DB_HOST_AKTIF}:${DB_PORT_AKTIF} tidak siap setelah ${BATAS} detik. Cek: docker compose logs db"
        fi
        sleep 1
    done
    log "MySQL siap setelah ${sudah} detik."
else
    log "DB_CONNECTION=${DB_CONNECTION:-mysql} — tidak menunggu MySQL."
fi

# ---------------------------------------------------------------------------
# 3. APP_KEY
# ---------------------------------------------------------------------------
APP_KEY_AKTIF="$(grep '^APP_KEY=' .env 2>/dev/null | head -n 1 | cut -d '=' -f 2- || true)"
if [ -z "$APP_KEY_AKTIF" ]; then
    log "APP_KEY kosong — membuat kunci aplikasi ..."
    php artisan key:generate --force --no-interaction
else
    log "APP_KEY sudah ada."
fi

# ---------------------------------------------------------------------------
# 4. Bersihkan cache
# ---------------------------------------------------------------------------
log "Membersihkan cache konfigurasi/rute/view ..."
php artisan config:clear --no-interaction
php artisan route:clear --no-interaction
php artisan view:clear --no-interaction

# ---------------------------------------------------------------------------
# 5. Migrasi (dengan percobaan ulang, supaya tidak ada galat balapan)
# ---------------------------------------------------------------------------
if [ "${JALANKAN_MIGRASI:-true}" = "true" ]; then
    log "Menjalankan migrasi ..."
    percobaan=1
    while :; do
        if php artisan migrate --force --no-interaction; then
            break
        fi
        if [ "$percobaan" -ge 3 ]; then
            gagal "migrasi gagal setelah 3 percobaan. Cek: docker compose logs db"
        fi
        percobaan=$((percobaan + 1))
        log "Migrasi gagal, mencoba lagi (${percobaan}/3) ..."
        sleep 3
    done
else
    log "JALANKAN_MIGRASI=false — migrasi dilewati."
fi

# ---------------------------------------------------------------------------
# 6. Data contoh (seeder idempoten)
# ---------------------------------------------------------------------------
if [ "${TANAM_DATA:-true}" = "true" ]; then
    log "Menanam data contoh (idempoten, tidak menghapus data) ..."
    php artisan db:seed --force --no-interaction
else
    log "TANAM_DATA=false — seeder dilewati."
fi

# ---------------------------------------------------------------------------
# 7. Cache untuk produksi
# ---------------------------------------------------------------------------
if [ "${APP_ENV:-local}" = "production" ]; then
    log "APP_ENV=production — membangun cache config/rute/view ..."
    php artisan config:cache --no-interaction
    php artisan route:cache --no-interaction
    php artisan view:cache --no-interaction
fi

# ---------------------------------------------------------------------------
# 8. Vite (opsional, untuk HMR)
# ---------------------------------------------------------------------------
if [ "${MULAI_VITE:-false}" = "true" ]; then
    log "Menjalankan Vite dev server di 0.0.0.0:${VITE_PORT:-5273} (latar belakang) ..."
    npm run dev -- --host 0.0.0.0 --port "${VITE_PORT:-5273}" &
else
    log "MULAI_VITE=false — Vite tidak dijalankan, aset memakai public/build."
fi

# ---------------------------------------------------------------------------
# 9. Perintah utama
# ---------------------------------------------------------------------------
log "Menjalankan: $*"
exec "$@"
