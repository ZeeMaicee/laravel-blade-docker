@php
    /** @var \App\Models\Catatan $catatan */
    $tombol = $tombol ?? 'Simpan';
@endphp

@if ($errors->any())
    <div class="rounded-lg border border-rose-500/30 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
        <p class="font-medium">Ada {{ $errors->count() }} isian yang perlu diperbaiki:</p>
        <ul class="mt-2 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $galat)
                <li>{{ $galat }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div>
    <label for="judul" class="block text-sm font-medium text-slate-300">
        Judul <span class="text-slate-500">(3–120 karakter)</span>
    </label>
    <input type="text" id="judul" name="judul" value="{{ old('judul', $catatan->judul) }}"
           class="mt-1 w-full rounded-lg border bg-slate-900 px-3 py-2 text-slate-100 placeholder-slate-500 outline-none transition focus:ring-2
                  @error('judul') border-rose-500 focus:ring-rose-500/30 @else border-slate-700 focus:border-indigo-500 focus:ring-indigo-500/25 @enderror"
           placeholder="Contoh: Rapat mingguan">
    @error('judul')
        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="isi" class="block text-sm font-medium text-slate-300">
        Isi <span class="text-slate-500">(maks 5.000 karakter)</span>
    </label>
    <textarea id="isi" name="isi" rows="6"
              class="mt-1 w-full rounded-lg border bg-slate-900 px-3 py-2 text-slate-100 placeholder-slate-500 outline-none transition focus:ring-2
                     @error('isi') border-rose-500 focus:ring-rose-500/30 @else border-slate-700 focus:border-indigo-500 focus:ring-indigo-500/25 @enderror"
              placeholder="Tulis isi catatan di sini...">{{ old('isi', $catatan->isi) }}</textarea>
    @error('isi')
        <p class="mt-1 text-xs text-rose-400">{{ $message }}</p>
    @enderror
</div>

<label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-300">
    {{-- Nilai bawaan 0 supaya checkbox yang tidak dicentang tetap terkirim. --}}
    <input type="hidden" name="selesai" value="0">
    <input type="checkbox" name="selesai" value="1" @checked(old('selesai', $catatan->selesai))
           class="h-4 w-4 rounded border-slate-600 bg-slate-900 text-indigo-500 focus:ring-indigo-500/40">
    Tandai sudah selesai
</label>

<div class="flex items-center gap-3 pt-2">
    <button type="submit"
            class="rounded-lg bg-indigo-500 px-5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-400">
        {{ $tombol }}
    </button>
    <a href="{{ route('catatan.index') }}"
       class="rounded-lg border border-slate-700 px-5 py-2 text-sm text-slate-300 transition hover:bg-slate-800">
        Batal
    </a>
</div>
