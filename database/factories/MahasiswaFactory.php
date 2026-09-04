<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mahasiswa>
 */
class MahasiswaFactory extends Factory
{
    protected $model = Mahasiswa::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'mahasiswa']),
            'prodi_id' => Prodi::factory(),
            'nim' => fake()->unique()->numerify('##########'),
            'angkatan' => fake()->numberBetween(2020, 2024),
            'status' => 'aktif',
        ];
    }
}
