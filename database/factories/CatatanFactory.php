<?php

namespace Database\Factories;

use App\Models\Catatan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Catatan>
 */
class CatatanFactory extends Factory
{
    protected $model = Catatan::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => rtrim($this->faker->sentence(4), '.'),
            'isi' => $this->faker->paragraph(),
            'selesai' => $this->faker->boolean(30),
        ];
    }
}
