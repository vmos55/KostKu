<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bookingDate = fake()->dateTimeBetween('-3 months', 'now');
        $checkInDate = fake()->dateTimeBetween($bookingDate, '+1 month');

        return [
            'booking_code' => fake()->unique()->numerify('BK-######'),
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'booking_date' => $bookingDate,
            'check_in_date' => $checkInDate,
            'check_out_date' => null,
            'status' => BookingStatus::Pending->value,
        ];
    }
}
