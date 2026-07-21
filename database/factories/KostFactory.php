<?php

namespace Database\Factories;

use App\Enums\KostStatus;
use App\Models\Kost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Kost>
 */
class KostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('KST-###'),
            'name' => 'Kost '.fake()->unique()->streetName(),
            'address' => fake()->address(),
            'city' => fake()->city(),
            'description' => fake()->paragraph(),
            'image' => null,
            'status' => KostStatus::Active->value,
        ];
    }
}
