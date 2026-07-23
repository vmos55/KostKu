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

        $this->assertSame(RoomStatus::Reserved, $room->fresh()->status);

        $this->actingAs($user, 'sanctum')->getJson('/api/bookings')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.room.id', $room->id);

        $this->getJson('/api/kosts')
            ->assertOk()
            ->assertJsonPath('data.0.rooms.0.available', true)
            ->assertJsonPath('data.0.rooms.0.status', RoomStatus::Reserved->value);
    }

    public function test_room_cannot_be_booked_twice(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $room = Room::factory()->for(Kost::factory())->create(['status' => RoomStatus::Available]);
        $payload = [
            'room_id' => $room->id,
            'check_in_date' => now()->addWeek()->toDateString(),
            'duration_months' => 2,
        ];

        $this->actingAs($firstUser, 'sanctum')->postJson('/api/bookings', $payload)->assertCreated();

        $this->actingAs($secondUser, 'sanctum')->postJson('/api/bookings', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('room_id');

        $laterPayload = [
            ...$payload,
            'check_in_date' => now()->addMonths(4)->toDateString(),
        ];

        $this->actingAs($secondUser, 'sanctum')->postJson('/api/bookings', $laterPayload)
            ->assertCreated();

        $this->assertDatabaseCount('bookings', 2);
    }

    public function test_public_api_checks_room_availability_by_date_range(): void
    {
        $room = Room::factory()->for(Kost::factory())->create(['status' => RoomStatus::Reserved]);
        Booking::factory()->for($room)->create([
            'status' => BookingStatus::Pending,
            'check_in_date' => now()->addMonth()->startOfDay(),
            'check_out_date' => now()->addMonths(2)->subDay()->startOfDay(),
        ]);

        $this->getJson('/api/rooms/'.$room->id.'/availability?'.http_build_query([
            'check_in_date' => now()->addMonth()->toDateString(),
            'duration_months' => 1,
        ]))->assertOk()
            ->assertJsonPath('available', false);

        $this->getJson('/api/rooms/'.$room->id.'/availability?'.http_build_query([
            'check_in_date' => now()->addMonths(3)->toDateString(),
            'duration_months' => 1,
        ]))->assertOk()
            ->assertJsonPath('available', true);
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

    public function test_complete_mobile_to_admin_booking_and_payment_flow_stays_in_sync(): void
    {
        Storage::fake('public');
        $tenant = User::factory()->create();
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->for(Kost::factory())->create(['status' => RoomStatus::Available]);

        $bookingResponse = $this->actingAs($tenant, 'sanctum')->postJson('/api/bookings', [
            'room_id' => $room->id,
            'check_in_date' => now()->addWeek()->toDateString(),
            'duration_months' => 2,
        ])->assertCreated();

        $booking = Booking::findOrFail($bookingResponse->json('data.id'));
        $this->assertSame(RoomStatus::Reserved, $room->fresh()->status);

        $this->actingAs($admin)->get(route('bookings.index'))
            ->assertOk()
            ->assertSee($booking->booking_code);

        $paymentResponse = $this->actingAs($tenant, 'sanctum')->postJson(
            '/api/bookings/'.$booking->id.'/payments',
            ['proof_image' => UploadedFile::fake()->image('bukti-alur.jpg')],
        )->assertCreated();

        $payment = $booking->payments()->findOrFail($paymentResponse->json('data.id'));
        $this->actingAs($admin)->patch(route('payments.approve', $payment))->assertSessionHas('success');

        $this->actingAs($tenant, 'sanctum')->getJson('/api/bookings')
            ->assertOk()
            ->assertJsonPath('data.0.status', BookingStatus::Approved->value)
            ->assertJsonPath('data.0.payments.0.status', 'approved');

        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
        $this->assertDatabaseHas('tenants', [
            'booking_id' => $booking->id,
            'user_id' => $tenant->id,
            'room_id' => $room->id,
        ]);
    }
}
