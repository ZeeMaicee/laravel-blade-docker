@extends('layouts.app')

@section('judul', 'Ubah catatan')

@section('isi')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-white">Ubah catatan</h1>
        <p class="mt-1 text-sm text-slate-400">Catatan #{{ $catatan->getKey() }}</p>

        <form method="POST" action="{{ route('catatan.update', $catatan) }}" class="mt-6 space-y-5 rounded-xl border border-slate-800 bg-slate-900/50 p-6">
            @csrf
            @method('PUT')
            @include('catatan._form', ['catatan' => $catatan, 'tombol' => 'Perbarui catatan'])
        </form>
    </div>
@endsection
