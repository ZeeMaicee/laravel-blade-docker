# Catatan kerja Node/Vite di template ini

Node **22** dipasang di dalam image aplikasi (`docker/php/Dockerfile`) dengan menyalin
binary dari image resmi `node:22-alpine`. Alasannya: repo alpine 3.24 pada image
`php:8.5-cli-alpine` sudah menyediakan Node 24, sedangkan template ini dipatok ke Node 22.

## Dua mode aset

| Mode | Yang terjadi | Kapan dipakai |
|---|---|---|
| **Build** (default) | `npm ci && npm run build` dijalankan saat `docker compose build`. Hasilnya `public/build/manifest.json`. Tidak ada proses Node yang menyala. | Sekali jalan, demo, produksi. |
| **Dev / HMR** | `MULAI_VITE=true` di `.env` → entrypoint menjalankan `npm run dev -- --host 0.0.0.0 --port 5273` di latar belakang, dan Laravel membaca `public/hot` sehingga memakai server Vite. | Saat mengubah CSS/JS. |

## Perintah yang sering dipakai

```sh
# build ulang aset (setelah mengubah resources/css atau resources/js, tanpa mode HMR)
docker compose exec app npm run build

# pasang dependensi baru setelah mengubah package.json
docker compose exec app npm install
docker compose exec app npm run build

# matikan mode HMR: hapus berkas penanda, kalau tidak aset akan menunjuk ke server Vite
docker compose exec app rm -f public/hot
```

`public/hot` dan `public/build` diabaikan git.
