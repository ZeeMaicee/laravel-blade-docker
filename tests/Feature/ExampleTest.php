<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Uji asap bawaan skeleton: halaman depan harus menjawab 200.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        // Tidak bergantung pada hasil `npm run build`.
        $this->withoutVite();

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
