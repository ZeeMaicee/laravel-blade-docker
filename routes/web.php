<?php

use App\Http\Controllers\CatatanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman arahan
|--------------------------------------------------------------------------
| Halaman depan template ini (bukan welcome bawaan Laravel). Isinya: URL
| penting, ringkasan cara pakai, dan pintasan menuju CRUD contoh.
*/
Route::get('/', function () {
    return view('beranda');
})->name('beranda');

/*
|--------------------------------------------------------------------------
| CRUD contoh "Catatan"
|--------------------------------------------------------------------------
| Satu resource controller biasa + Blade (daftar, tambah, ubah, hapus) dengan
| validasi di sisi server. Route `show` tidak dipakai.
*/
Route::resource('catatan', CatatanController::class)->except(['show']);

// Kesehatan aplikasi tersedia di /up (didaftarkan di bootstrap/app.php).
