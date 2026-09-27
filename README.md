# laravel-blade-docker

**Laravel + Blade + Docker** — template Laravel yang langsung jalan setelah di-clone:
satu perintah untuk menyalakan aplikasi, MySQL, dan (opsional) Vite. Isinya halaman depan
berupa **halaman arahan** milik template ini (bukan `welcome.blade.php` bawaan Laravel),
ditambah **CRUD contoh "Catatan"** (daftar, tambah, ubah, hapus) memakai controller + Blade
biasa dengan validasi di sisi server dan Tailwind. Container `app` menyiapkan semuanya
sendiri saat menyala: menulis `.env`, menunggu MySQL benar-benar siap, membuat `APP_KEY`,
menjalankan migrasi, lalu menanam data contoh. Ada uji fitur untuk halaman depan dan CRUD.

---

## 1. Prasyarat

- **Docker Desktop** (sudah termasuk `docker compose`) — Docker 24+ dan Compose v2.20+.
- Tidak perlu memasang PHP, Composer, Node, atau MySQL di komputer Anda: semuanya ada di
  dalam container.

Dicek cepat:

```sh
docker --version && docker compose version
```

## 2. Cara pakai (3 perintah)

```sh
cp .env.example .env                 # 1. buat berkas .env, lalu ubah DB_PASSWORD & MYSQL_ROOT_PASSWORD
docker compose up -d --build         # 2. bangun image dan nyalakan app + db
docker compose exec app php artisan test   # 3. buktikan aplikasi sehat (uji hijau)
```

Lalu buka <http://localhost:8300>.

Kenapa ada langkah `cp .env.example .env`? Karena sandi basis data **tidak** ditulis di
repo. `docker-compose.yml` membacanya dengan pola `${DB_PASSWORD:?pesan}` — kalau `.env`
belum ada atau sandinya kosong, compose berhenti dengan pesan yang jelas, bukan diam-diam
memakai sandi bawaan.

Kalau ingin berhenti dan menghapus basis datanya:

```sh
docker compose down -v
```

## 3. URL dan akun bawaan

| Apa | Alamat |
|---|---|
| Aplikasi (halaman arahan) | <http://localhost:8300> |
| CRUD contoh | <http://localhost:8300/catatan> |
| Pemeriksaan kesehatan | <http://localhost:8300/up> |
| Vite (HMR), hanya kalau `MULAI_VITE=true` | <http://localhost:5273> |
| MySQL dari komputer Anda (opsional, untuk GUI) | `127.0.0.1:3317` |
| Adminer (opsional, lihat bagian 9) | <http://localhost:8390> |

**Akun bawaan:** template ini sengaja **tanpa autentikasi** — semua halaman, termasuk CRUD
Catatan, terbuka. Seeder tetap membuat satu pengguna contoh untuk Anda pakai kalau nanti
menambahkan login:

| Surel | Sandi |
|---|---|
| `admin@contoh.test` | `password` |

Data contoh yang ditanam (`TANAM_DATA=true`) hanya tiga catatan dan satu pengguna di atas.
Seeder-nya idempoten: bisa dijalankan berkali-kali tanpa menggandakan data dan tanpa
menghapus apa pun.

## 4. Menjalankan uji

```sh
docker compose exec app php artisan test
```

Uji berjalan di **SQLite in-memory** (lihat `phpunit.xml`), jadi tidak menyentuh MySQL milik
container dan tidak menghapus data pengembangan Anda. Berkas uji:

```
tests/Feature/BerandaTest.php     # halaman depan 200, bukan welcome bawaan, /up 200
tests/Feature/CatatanCrudTest.php # simpan -> muncul di daftar, validasi, ubah, hapus
```

Perintah lain yang sering dipakai:

```sh
docker compose exec app php artisan migrate:fresh --seed   # bangun ulang basis data + data contoh
docker compose exec app php artisan route:list
```

## 5. HMR / Vite

Aset (Tailwind + JS) di-build **sekali saat image dibuat** (`npm ci && npm run build`), jadi
aplikasi tampil rapi tanpa proses Node yang menyala. Untuk mengubah CSS/JS dengan HMR:

```sh
# di .env: MULAI_VITE=true, lalu
docker compose up -d

# kembali ke mode build:
docker compose down
# di .env: MULAI_VITE=false, lalu
docker compose up -d
```

Saat Vite menyala, Laravel membaca berkas `public/hot` dan memakai server Vite di
`http://localhost:5273`. Kalau Vite dimatikan paksa, hapus penandanya
(`docker compose exec app rm -f public/hot`) supaya aset kembali memakai `public/build`.
Node yang dipakai adalah **Node 22**. Catatan lengkap ada di `docker/node/README.md`.

## 6. Masuk ke dalam container

```sh
docker compose exec app sh            # shell di container aplikasi
docker compose exec app php artisan   # perintah artisan apa pun
docker compose exec db mysql -ularavel -p laravel
docker compose logs -f app            # ikuti log aplikasi (termasuk log Vite)
```

## 7. Catatan produksi (jujur)

Container `app` menjalankan **`php artisan serve`**, yaitu server bawaan PHP. Itu **alat
pengembangan**, bukan server produksi:

- hanya satu proses (tanpa worker), tidak menangani permintaan bersamaan dengan baik;
- tidak ada TLS, tidak ada kompresi, tidak ada penyajian berkas statis yang efisien;
- tidak ada mekanisme restart kalau proses mati.

Untuk produksi, arah yang benar:

1. Ganti `CMD` container menjadi **php-fpm** (`php-fpm` / `php-fpm8.5`), lalu letakkan
   **nginx** (atau Apache) di depannya sebagai reverse proxy — nginx yang melayani
   `public/` dan meneruskan PHP ke php-fpm lewat FastCGI.
2. Aktifkan cache: set `APP_ENV=production` dan `APP_DEBUG=false`; entrypoint akan
   menjalankan `config:cache`, `route:cache`, dan `view:cache`.
3. Jangan publish port MySQL ke host (`3317`) di server.
4. Bangun aset di tahap build image (`npm run build`) dan sajikan `public/build` dari nginx,
   bukan lewat Vite.

## 8. Isi `docker/`

```
docker/
├── php/
│   ├── Dockerfile      # PHP 8.5-cli-alpine + Node 22 + Composer + ekstensi (pdo_mysql, intl, zip, bcmath, pcntl, exif)
│   ├── php.ini         # setelan PHP aplikasi (memory, upload, zona waktu, OPcache)
│   └── entrypoint.sh   # .env -> tunggu MySQL -> APP_KEY -> bersihkan cache -> migrate -> seed -> Vite -> exec
├── mysql/
│   └── my.cnf          # utf8mb4, zona waktu +07:00, sql_mode
└── node/
    └── README.md       # catatan kerja Node/Vite
```

`entrypoint.sh` selalu berakhir dengan `exec "$@"` supaya sinyal dan kode keluar tetap
diteruskan ke perintah utama container.

## 9. Variabel lingkungan

Semua diatur di `.env` (disalin dari `.env.example`, diabaikan git).

| Variabel | Bawaan di `.env.example` | Keterangan |
|---|---|---|
| `APP_NAME` | `"Laravel Blade Docker"` | Nama aplikasi. |
| `APP_ENV` | `local` | `production` memicu `config:cache route:cache view:cache`. |
| `APP_DEBUG` | `true` | Jangan `true` di produksi. |
| `APP_TIMEZONE` | `Asia/Jakarta` | Dipakai `config/app.php` dan `date.timezone`. |
| `APP_URL` | `http://localhost:8300` | Harus sama dengan `APP_PORT` di host. |
| `APP_LOCALE` | `id` | Lokal aplikasi. |
| `DB_CONNECTION` | `mysql` | Host-nya adalah nama service: `db`. |
| `DB_HOST` | `db` | Jangan `127.0.0.1` — itu akan menunjuk ke container aplikasi sendiri. |
| `DB_PORT` | `3306` | Port di dalam jaringan container. |
| `DB_DATABASE` | `laravel` | Basis data yang dibuat otomatis. |
| `DB_USERNAME` | `laravel` | Pengguna MySQL. |
| `DB_PASSWORD` | *contoh* | **Wajib diganti.** Dipakai compose & aplikasi. |
| `MYSQL_ROOT_PASSWORD` | *contoh* | **Wajib diganti.** Hanya untuk MySQL. |
| `SESSION_DRIVER` | `database` | Tabel `sessions` sudah ikut migrasi. |
| `SESSION_LIFETIME` | `120` | Menit. |
| `CACHE_STORE` | `database` | Tabel `cache`. |
| `QUEUE_CONNECTION` | `database` | Tabel `jobs`. |
| `JALANKAN_MIGRASI` | `true` | `php artisan migrate --force` saat container menyala. |
| `TANAM_DATA` | `true` | `php artisan db:seed --force` (idempoten). |
| `MULAI_VITE` | `false` | `true` → `npm run dev --host 0.0.0.0 --port 5273` di latar. |
| `APP_PORT` | `8300` | Port aplikasi di komputer Anda (dalam container selalu `8000`). |
| `DB_PORT_PUBLISHED` | `3317` | Port MySQL di komputer Anda (opsional). |
| `VITE_PORT` | `5273` | Port Vite di komputer Anda. |

### Port (tiga template bisa menyala bersamaan)

| | Aplikasi | MySQL (host) | Vite |
|---|---|---|---|
| **laravel-blade-docker** | **8300** | **3317** | **5273** |
| laravel-livewire-docker | 8302 | 3319 | 5275 |
| laravel-react-docker | 8301 | 3318 | 5274 |

Container: `laravel-blade-app`, `laravel-blade-db`. Volume data: `laravel-blade-data-mysql`.

### Alat bantu opsional

Adminer **tidak** menyala secara bawaan (berada di balik profil compose):

```sh
docker compose --profile alat up -d     # Adminer di http://localhost:8390
```

Server: `db`, pengguna: `laravel`, sandi: `DB_PASSWORD` Anda dari `.env`.

---

## Struktur singkat

```
app/Http/Controllers/CatatanController.php   # CRUD contoh
app/Models/Catatan.php                       # model, tabel `catatan`
resources/views/beranda.blade.php            # halaman arahan
resources/views/catatan/                     # index / create / edit / _form
resources/views/layouts/app.blade.php        # layout Blade + Tailwind
routes/web.php                               # GET / dan resource catatan
database/migrations/..._create_catatan_table.php
database/seeders/IsiContohSeeder.php         # idempoten
tests/Feature/{BerandaTest,CatatanCrudTest}.php
docker-compose.yml · docker/ · .env.example · phpunit.xml · vite.config.js
```
