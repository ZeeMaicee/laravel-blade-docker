<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Beranda') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-200 antialiased">
<div class="flex min-h-full flex-col">

    <header class="border-b border-slate-800/80 bg-slate-900/60">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-4 px-6 py-4">
            <a href="{{ route('beranda') }}" class="flex items-center gap-3 font-semibold text-white">
                <span class="grid h-9 w-9 place-items-center rounded-lg bg-indigo-500/15 ring-1 ring-indigo-500/30">📝</span>
                <span>{{ config('app.name') }}</span>
            </a>

            <nav class="flex items-center gap-1 text-sm">
                @php
                    $kelas = 'rounded-md px-3 py-1.5 transition';
                    $aktif = 'bg-slate-800 text-white';
                    $nonaktif = 'text-slate-300 hover:bg-slate-800 hover:text-white';
                @endphp

                <a href="{{ route('beranda') }}" class="{{ $kelas }} {{ request()->routeIs('beranda') ? $aktif : $nonaktif }}">Beranda</a>
                <a href="{{ route('catatan.index') }}" class="{{ $kelas }} {{ request()->routeIs('catatan.*') ? $aktif : $nonaktif }}">Catatan</a>
                <a href="{{ url('/up') }}" target="_blank" rel="noopener" class="{{ $kelas }} {{ $nonaktif }}">Kesehatan</a>
            </nav>
        </div>
    </header>

    @if (session('sukses'))
        <div class="mx-auto mt-6 w-full max-w-5xl px-6">
            <div class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                {{ session('sukses') }}
            </div>
        </div>
    @endif

    <main class="mx-auto w-full max-w-5xl flex-1 px-6 py-10">
        @yield('isi')
    </main>

    <footer class="mx-auto w-full max-w-5xl px-6 pb-10 pt-4 text-xs text-slate-500">
        Laravel {{ app()->version() }} · PHP {{ PHP_VERSION }} · template <span class="text-slate-400">laravel-blade-docker</span>
    </footer>

</div>
</body>
</html>
