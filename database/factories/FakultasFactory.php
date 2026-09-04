<?php

namespace Database\Factories;

use App\Models\Fakultas;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Fakultas>
 */
class FakultasFactory extends Factory
{
    protected $model = Fakultas::class;

    public function definition(): array
    {
        return [
            'nama' => fake()->randomElement([
                'Fakultas Teknik',
                'Fakultas Ekonomi',
                'Fakultas Ilmu Komputer',
                'Fakultas Hukum',
                'Fakultas Kedokteran',
            ]) . ' ' . fake()->randomNumber(2),
        ];
    }
}
