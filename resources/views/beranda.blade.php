@extends('layouts.app')

@section('judul', 'Beranda')

@section('isi')
    <section class="rounded-2xl border border-slate-800 bg-gradient-to-br from-slate-900 to-slate-950 p-8">
        <p class="text-xs font-semibold uppercase tracking-widest text-indigo-400">Template siap pakai</p>
        <h1 class="mt-3 text-3xl font-bold text-white sm:text-4xl">Laravel + Blade + Docker</h1>
        <p class="mt-4 max-w-2xl text-slate-300">
            Halaman ini adalah <span class="text-white">halaman arahan</span> milik template — bukan
            <code class="rounded bg-slate-800 px-1.5 py-0.5 text-sm text-indigo-300">welcome.blade.php</code> bawaan Laravel.
            Aplikasi sudah tersambung ke MySQL, migrasi sudah jalan, dan data contoh sudah ditanam.
        </p>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('catatan.index') }}"
               class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400">
                Buka CRUD contoh &rarr;
            </a>
            <a href="{{ url('/up') }}" target="_blank" rel="noopener"
               class="rounded-lg border border-slate-700 px-4 py-2 text-sm font-semibold text-slate-200 transition hover:bg-slate-800">
                Cek kesehatan (/up)
            </a>
        </div>
    </section>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @php
            $kartu = [
                ['label' => 'Aplikasi', 'nilai' => config('app.url'), 'catatan' => 'port '.env('APP_PORT', '8300')],
                ['label' => 'Basis data', 'nilai' => config('database.connections.mysql.database'), 'catatan' => 'host `db` · MySQL 8.4'],
                ['label' => 'Zona waktu', 'nilai' => config('app.timezone'), 'catatan' => 'APP_TIMEZONE'],
                ['label' => 'Lingkungan', 'nilai' => config('app.env'), 'catatan' => 'APP_DEBUG='.var_export(config('app.debug'), true)],
            ];
        @endphp

        @foreach ($kartu as $k)
            <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-5">
                <p class="text-xs uppercase tracking-wide text-slate-500">{{ $k['label'] }}</p>
                <p class="mt-2 truncate font-mono text-sm text-white" title="{{ $k['nilai'] }}">{{ $k['nilai'] }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $k['catatan'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="mt-10 grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
            <h2 class="text-lg font-semibold text-white">Cara pakai (3 perintah)</h2>
            <ol class="mt-4 space-y-3 text-sm text-slate-300">
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-indigo-500/20 text-xs font-bold text-indigo-300">1</span>
                    <span><code class="rounded bg-slate-800 px-1.5 py-0.5 text-indigo-300">cp .env.example .env</code> lalu ubah <code class="text-indigo-300">DB_PASSWORD</code> dan <code class="text-indigo-300">MYSQL_ROOT_PASSWORD</code>.</span>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-indigo-500/20 text-xs font-bold text-indigo-300">2</span>
                    <span><code class="rounded bg-slate-800 px-1.5 py-0.5 text-indigo-300">docker compose up -d --build</code></span>
                </li>
                <li class="flex gap-3">
                    <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full bg-indigo-500/20 text-xs font-bold text-indigo-300">3</span>
                    <span><code class="rounded bg-slate-800 px-1.5 py-0.5 text-indigo-300">docker compose exec app php artisan test</code></span>
                </li>
            </ol>
            <p class="mt-4 text-sm text-slate-400">
                Container menunggu MySQL benar-benar sehat sebelum migrasi, lalu membuat
                <code class="text-indigo-300">APP_KEY</code> kalau masih kosong.
            </p>
        </div>

        <div class="rounded-xl border border-slate-800 bg-slate-900/50 p-6">
            <h2 class="text-lg font-semibold text-white">Yang ada di template ini</h2>
            <ul class="mt-4 space-y-3 text-sm text-slate-300">
                <li>• <strong class="text-white">Halaman arahan</strong> (halaman yang sedang Anda lihat).</li>
                <li>• <strong class="text-white">CRUD "Catatan"</strong> — daftar, tambah, ubah, hapus, validasi server, Blade + Tailwind.</li>
                <li>• <strong class="text-white">Uji fitur</strong> untuk halaman depan dan CRUD (<code class="text-indigo-300">tests/Feature</code>).</li>
                <li>• <strong class="text-white">Docker Compose</strong> — app (PHP 8.5 + Node 22) dan db (MySQL 8.4) dengan healthcheck.</li>
                <li>• <strong class="text-white">Entrypoint pintar</strong> — <code class="text-indigo-300">docker/php/entrypoint.sh</code>.</li>
            </ul>
        </div>
    </section>

    <section class="mt-10 rounded-xl border border-amber-500/25 bg-amber-500/5 p-6">
        <h2 class="text-lg font-semibold text-amber-200">Catatan produksi (jujur)</h2>
        <p class="mt-3 text-sm text-amber-100/90">
            Container ini menjalankan <code class="rounded bg-slate-800 px-1.5 py-0.5">php artisan serve</code>,
            yaitu server bawaan PHP. Itu <strong>alat pengembangan</strong>, bukan untuk produksi:
            satu proses, tanpa worker, tanpa TLS, tanpa penanganan berkas statis yang efisien.
            Untuk produksi gunakan <strong>php-fpm + nginx</strong> (atau Apache), lalu matikan
            <code class="rounded bg-slate-800 px-1.5 py-0.5">php artisan serve</code> sebagai perintah container.
            Detailnya ada di <code class="rounded bg-slate-800 px-1.5 py-0.5">README.md</code> bagian <em>Catatan produksi</em>.
        </p>
    </section>
@endsection
