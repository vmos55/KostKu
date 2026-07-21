<?php

namespace Database\Factories;

use App\Enums\RoomStatus;
use App\Models\Kost;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kost_id' => Kost::factory(),
            'room_number' => fake()->unique()->bothify('?--##'),
            'floor' => fake()->numberBetween(1, 5),
            'price' => fake()->randomElement([1500000, 1750000, 2000000, 2500000]),
            'status' => RoomStatus::Available->value,
        ];
    }
}
