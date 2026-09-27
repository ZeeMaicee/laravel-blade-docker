@extends('layouts.app')

@section('judul', 'Tambah catatan')

@section('isi')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-white">Tambah catatan</h1>
        <p class="mt-1 text-sm text-slate-400">
            Semua isian divalidasi di server (lihat <code class="text-indigo-300">CatatanController@validasi</code>).
        </p>

        <form method="POST" action="{{ route('catatan.store') }}" class="mt-6 space-y-5 rounded-xl border border-slate-800 bg-slate-900/50 p-6">
            @csrf
            @include('catatan._form', ['catatan' => $catatan, 'tombol' => 'Simpan catatan'])
        </form>
    </div>
@endsection
