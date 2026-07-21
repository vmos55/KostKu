<?php

namespace Database\Factories;

use App\Enums\IdentityVerificationStatus;
use App\Enums\TenantStatus;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'room_id' => Room::factory(),
            'booking_id' => null,
            'identity_number' => fake()->unique()->numerify('################'),
            'identity_verification_status' => IdentityVerificationStatus::Pending->value,
            'check_in' => fake()->dateTimeBetween('-1 year', 'now'),
            'check_out' => null,
            'status' => TenantStatus::Active->value,
        ];
    }
}
