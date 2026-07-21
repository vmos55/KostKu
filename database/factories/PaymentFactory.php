<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_code' => fake()->unique()->numerify('TRX-####'),
            'booking_id' => Booking::factory(),
            'amount' => fake()->randomElement([1500000, 1750000, 2000000, 2500000]),
            'proof_image' => 'payments/proofs/'.fake()->uuid().'.jpg',
            'status' => PaymentStatus::Pending->value,
            'submitted_at' => now(),
        ];
    }
}
