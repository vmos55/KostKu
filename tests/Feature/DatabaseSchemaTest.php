<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\KostStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_domain_records_and_relationships_can_be_persisted(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();
        $kost = Kost::factory()->create();
        $room = Room::factory()->for($kost)->create();
        $booking = Booking::factory()->for($user)->for($room)->create([
            'reviewed_by' => $admin->id,
        ]);
        $payment = Payment::factory()->for($booking)->create([
            'verified_by' => $admin->id,
        ]);
        $tenant = Tenant::factory()->for($user)->for($room)->for($booking)->create();

        $this->assertTrue($kost->rooms->contains($room));
        $this->assertTrue($room->bookings->contains($booking));
        $this->assertTrue($booking->payments->contains($payment));
        $this->assertTrue($user->tenants->contains($tenant));
        $this->assertTrue($admin->reviewedBookings->contains($booking));
        $this->assertTrue($admin->verifiedPayments->contains($payment));
    }

    public function test_status_fields_are_cast_to_domain_enums(): void
    {
        $kost = Kost::factory()->create();
        $room = Room::factory()->for($kost)->create();
        $booking = Booking::factory()->for($room)->create();
        $payment = Payment::factory()->for($booking)->create();
        $tenant = Tenant::factory()->for($room)->create();

        $this->assertSame(KostStatus::Active, $kost->status);
        $this->assertSame(RoomStatus::Available, $room->status);
        $this->assertSame(BookingStatus::Pending, $booking->status);
        $this->assertSame(PaymentStatus::Pending, $payment->status);
        $this->assertSame(TenantStatus::Active, $tenant->status);
    }
}
