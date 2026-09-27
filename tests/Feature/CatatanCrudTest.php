<?php

namespace Tests\Feature;

use App\Models\Catatan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatatanCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_daftar_catatan_menjawab_200(): void
    {
        Catatan::factory()->create(['judul' => 'Catatan dari uji']);

        $this->get(route('catatan.index'))
            ->assertOk()
            ->assertSee('Catatan dari uji');
    }

    public function test_form_tambah_catatan_menjawab_200(): void
    {
        $this->get(route('catatan.create'))->assertOk()->assertSee('Tambah catatan');
    }

    public function test_catatan_tersimpan_lalu_muncul_di_daftar(): void
    {
        $respons = $this->post(route('catatan.store'), [
            'judul' => 'Belanja mingguan',
            'isi' => 'Beras, telur, kopi.',
            'selesai' => '1',
        ]);

        $respons->assertRedirect(route('catatan.index'));
        $respons->assertSessionHas('sukses');

        $this->assertDatabaseHas('catatan', [
            'judul' => 'Belanja mingguan',
            'selesai' => true,
        ]);

        $this->get(route('catatan.index'))
            ->assertOk()
            ->assertSee('Belanja mingguan');
    }

    public function test_validasi_menolak_isian_kosong(): void
    {
        $respons = $this->from(route('catatan.create'))->post(route('catatan.store'), [
            'judul' => '',
            'isi' => '',
        ]);

        $respons->assertRedirect(route('catatan.create'));
        $respons->assertSessionHasErrors(['judul', 'isi']);

        $this->assertDatabaseCount('catatan', 0);
    }

    public function test_validasi_menolak_judul_terlalu_pendek(): void
    {
        $respons = $this->from(route('catatan.create'))->post(route('catatan.store'), [
            'judul' => 'ab',
            'isi' => 'Isi yang cukup panjang.',
        ]);

        $respons->assertSessionHasErrors('judul');

        $this->assertDatabaseCount('catatan', 0);
    }

    public function test_catatan_bisa_diubah(): void
    {
        $catatan = Catatan::factory()->create([
            'judul' => 'Judul lama',
            'isi' => 'Isi lama.',
            'selesai' => false,
        ]);

        $respons = $this->put(route('catatan.update', $catatan), [
            'judul' => 'Judul baru',
            'isi' => 'Isi baru.',
            'selesai' => '1',
        ]);

        $respons->assertRedirect(route('catatan.index'));

        $this->assertDatabaseHas('catatan', [
            'id' => $catatan->getKey(),
            'judul' => 'Judul baru',
            'selesai' => true,
        ]);
    }

    public function test_catatan_bisa_dihapus(): void
    {
        $catatan = Catatan::factory()->create();

        $respons = $this->delete(route('catatan.destroy', $catatan));

        $respons->assertRedirect(route('catatan.index'));
        $respons->assertSessionHas('sukses');

        $this->assertDatabaseCount('catatan', 0);
        $this->assertDatabaseMissing('catatan', ['id' => $catatan->getKey()]);
    }
}
