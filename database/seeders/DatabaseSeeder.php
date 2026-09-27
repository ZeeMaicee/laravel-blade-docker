<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Entrypoint seeder (dijalankan oleh `php artisan db:seed`).
 * Semua seeder di sini harus idempoten — lihat IsiContohSeeder.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            IsiContohSeeder::class,
        ]);
    }
}
