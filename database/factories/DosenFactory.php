<?php

namespace Database\Factories;

use App\Models\Dosen;
use App\Models\Prodi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Dosen>
 */
class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => 'dosen']),
            'nidn' => fake()->unique()->numerify('##########'),
            'prodi_id' => Prodi::factory(),
        ];
    }
}
