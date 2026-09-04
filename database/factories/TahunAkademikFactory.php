<?php

namespace Database\Factories;

use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TahunAkademik>
 */
class TahunAkademikFactory extends Factory
{
    protected $model = TahunAkademik::class;

    public function definition(): array
    {
        $tahun = fake()->numberBetween(2020, 2024);
        return [
            'tahun' => "{$tahun}/" . ($tahun + 1),
            'semester' => fake()->randomElement(['Ganjil', 'Genap']),
            'is_active' => false,
        ];
    }
}
