@extends('layouts.app')

@section('judul', 'Catatan')

@section('isi')
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Catatan</h1>
            <p class="mt-1 text-sm text-slate-400">
                CRUD contoh: daftar, tambah, ubah, hapus — controller + Blade biasa, validasi di sisi server.
            </p>
        </div>

        <a href="{{ route('catatan.create') }}"
           class="rounded-lg bg-indigo-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400">
            + Tambah catatan
        </a>
    </div>

    @if ($catatan->isEmpty())
        <div class="mt-8 rounded-xl border border-dashed border-slate-700 bg-slate-900/40 p-10 text-center">
            <p class="text-slate-300">Belum ada catatan.</p>
            <p class="mt-1 text-sm text-slate-500">
                Tekan <span class="text-slate-300">Tambah catatan</span>, atau nyalakan
                <code class="text-indigo-300">TANAM_DATA=true</code> supaya data contoh ditanam otomatis.
            </p>
        </div>
    @else
        <div class="mt-6 overflow-hidden rounded-xl border border-slate-800">
            <table class="w-full border-collapse text-left text-sm">
                <thead class="bg-slate-900/80 text-xs uppercase tracking-wide text-slate-400">
                    <tr>
                        <th class="px-4 py-3 font-medium">Judul</th>
                        <th class="px-4 py-3 font-medium">Isi</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Diperbarui</th>
                        <th class="px-4 py-3 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @foreach ($catatan as $baris)
                        <tr class="bg-slate-950/40 transition hover:bg-slate-900/60">
                            <td class="px-4 py-3 font-medium text-white">{{ $baris->judul }}</td>
                            <td class="max-w-md px-4 py-3 text-slate-400">{{ Str::limit($baris->isi, 90) }}</td>
                            <td class="px-4 py-3">
                                @if ($baris->selesai)
                                    <span class="rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-xs text-emerald-300">Selesai</span>
                                @else
                                    <span class="rounded-full border border-slate-600/40 bg-slate-700/20 px-2.5 py-1 text-xs text-slate-300">Terbuka</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $baris->updated_at?->diffForHumans() }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('catatan.edit', $baris) }}"
                                       class="rounded-md border border-slate-700 px-3 py-1.5 text-xs text-slate-200 transition hover:bg-slate-800">
                                        Ubah
                                    </a>

                                    <form method="POST" action="{{ route('catatan.destroy', $baris) }}"
                                          onsubmit="return confirm('Hapus catatan ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-md border border-rose-500/40 px-3 py-1.5 text-xs text-rose-300 transition hover:bg-rose-500/10">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $catatan->links() }}
        </div>
    @endif
@endsection
