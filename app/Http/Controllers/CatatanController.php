<?php

namespace App\Http\Controllers;

use App\Models\Catatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * CRUD contoh "Catatan" dengan controller + Blade biasa.
 * Semua validasi dilakukan di sisi server lewat `$request->validate()`.
 */
class CatatanController extends Controller
{
    public function index(): View
    {
        $catatan = Catatan::query()->latest()->paginate(10);

        return view('catatan.index', ['catatan' => $catatan]);
    }

    public function create(): View
    {
        return view('catatan.create', ['catatan' => new Catatan]);
    }

    public function store(Request $request): RedirectResponse
    {
        Catatan::create($this->validasi($request));

        return redirect()
            ->route('catatan.index')
            ->with('sukses', 'Catatan berhasil disimpan.');
    }

    public function edit(Catatan $catatan): View
    {
        return view('catatan.edit', ['catatan' => $catatan]);
    }

    public function update(Request $request, Catatan $catatan): RedirectResponse
    {
        $catatan->update($this->validasi($request));

        return redirect()
            ->route('catatan.index')
            ->with('sukses', 'Catatan berhasil diperbarui.');
    }

    public function destroy(Catatan $catatan): RedirectResponse
    {
        $catatan->delete();

        return redirect()
            ->route('catatan.index')
            ->with('sukses', 'Catatan berhasil dihapus.');
    }

    /**
     * Aturan validasi yang dipakai `store()` dan `update()`.
     *
     * @return array<string, mixed>
     */
    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'min:3', 'max:120'],
            'isi' => ['required', 'string', 'max:5000'],
            'selesai' => ['nullable', 'boolean'],
        ]);

        // Checkbox yang tidak dicentang tidak ikut terkirim.
        $data['selesai'] = $request->boolean('selesai');

        return $data;
    }
}
