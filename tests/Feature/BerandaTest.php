<?php

namespace Tests\Feature;

use Tests\TestCase;

class BerandaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Uji tidak bergantung pada hasil `npm run build`.
        $this->withoutVite();
    }

    public function test_halaman_depan_menjawab_200(): void
    {
        $respons = $this->get('/');

        $respons->assertOk();
        $respons->assertSee('Laravel + Blade + Docker');
        $respons->assertSee('Buka CRUD contoh', false);
    }

    public function test_halaman_depan_bukan_welcome_bawaan_laravel(): void
    {
        // Penanda halaman arahan milik template.
        $this->get('/')->assertOk()->assertSee('halaman arahan');
    }

    public function test_halaman_kesehatan_menjawab_200(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_halaman_depan_memuat_pintasan_ke_catatan(): void
    {
        $this->get('/')->assertOk()->assertSee(route('catatan.index'), false);
    }
}
