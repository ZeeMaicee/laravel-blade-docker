<?php

namespace Database\Seeders;

use App\Models\Catatan;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder contoh. Dijalankan otomatis oleh entrypoint (TANAM_DATA=true).
 *
 * WAJIB idempoten: seeder ini dijalankan pada SETIAP kali container menyala,
 * jadi tidak boleh gagal karena data sudah ada, dan tidak boleh menghapus
 * data milik pengguna.
 */
class IsiContohSeeder extends Seeder
{
    public function run(): void
    {
        // Satu pengguna contoh. Berguna kalau nanti Anda menambahkan autentikasi.
        User::firstOrCreate(
            ['email' => 'admin@contoh.test'],
            [
                'name' => 'Admin Contoh',
                'password' => 'password',
            ],
        );

        $contoh = [
            [
                'judul' => 'Selamat datang di template',
                'isi' => 'Halaman ini adalah CRUD contoh. Silakan tambah, ubah, dan hapus catatan untuk mencoba.',
                'selesai' => false,
            ],
            [
                'judul' => 'Cek koneksi basis data',
                'isi' => 'Kalau catatan ini muncul, berarti container aplikasi sudah tersambung ke MySQL 8.4.',
                'selesai' => true,
            ],
            [
                'judul' => 'Jalankan pengujian',
                'isi' => 'Perintah: docker compose exec app php artisan test',
                'selesai' => false,
            ],
        ];

        foreach ($contoh as $baris) {
            // firstOrCreate supaya aman dijalankan berkali-kali.
            Catatan::firstOrCreate(
                ['judul' => $baris['judul']],
                ['isi' => $baris['isi'], 'selesai' => $baris['selesai']],
            );
        }
    }
}
