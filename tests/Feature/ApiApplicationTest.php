<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\KostStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_can_register_login_and_logout_with_api_token(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Budi Tenant',
            'email' => 'budi@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_name' => 'Android Test',
        ])->assertCreated()
            ->assertJsonPath('user.email', 'budi@example.com')
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'phone', 'role']]);

        $token = $response->json('token');
        $this->withToken($token)->postJson('/api/logout')->assertOk();

        $this->postJson('/api/login', [
            'email' => 'budi@example.com',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['token', 'user']);
    }

    public function test_public_api_only_lists_active_kosts(): void
    {
        $active = Kost::factory()->create(['status' => KostStatus::Active]);
        Room::factory()->for($active)->create(['status' => RoomStatus::Available]);
        Kost::factory()->create(['status' => KostStatus::Inactive]);

        $this->getJson('/api/kosts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $active->id)
            ->assertJsonPath('data.0.rooms.0.available', true);
    }

    public function test_authenticated_tenant_can_create_and_read_own_booking(): void
    {
        $user = User::factory()->create();
        $room = Room::factory()->for(Kost::factory())->create([
            'status' => RoomStatus::Available,
        ]);

        $this->actingAs($user, 'sanctum')->postJson('/api/bookings', [
            'room_id' => $room->id,
            'check_in_date' => now()->addWeek()->toDateString(),
            'duration_months' => 3,
        ])->assertCreated()
            ->assertJsonPath('data.status', BookingStatus::Pending->value)
            ->assertJsonPath('data.duration_months', 3);

        $this->actingAs($user, 'sanctum')->getJson('/api/bookings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.room.id', $room->id);
    }

    public function test_tenant_cannot_read_another_users_booking(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->for($owner)->create();

        $this->actingAs($otherUser, 'sanctum')
            ->getJson('/api/bookings/'.$booking->id)
            ->assertNotFound();
    }

    public function test_tenant_can_upload_payment_proof_for_own_booking(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $booking = Booking::factory()
            ->for($user)
            ->for(Room::factory()->for(Kost::factory()))
            ->create([
                'check_in_date' => now()->addWeek(),
                'check_out_date' => now()->addWeek()->addMonths(2)->subDay(),
            ]);

        $response = $this->actingAs($user, 'sanctum')->postJson(
            '/api/bookings/'.$booking->id.'/payments',
            ['proof_image' => UploadedFile::fake()->image('bukti.jpg')],
        );

        $response->assertCreated()
            ->assertJsonPath('data.booking_id', $booking->id)
            ->assertJsonPath('data.status', 'pending');

        $path = $booking->payments()->firstOrFail()->proof_image;
        Storage::disk('public')->assertExists($path);
    }
}
