<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminApplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_and_open_all_main_pages(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        foreach (['dashboard', 'kosts.index', 'rooms.index', 'bookings.index', 'tenants.index', 'payments.index', 'reports.index', 'settings.edit'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_admin_can_update_settings(): void
    {
        $admin = User::factory()->admin()->create(['password' => 'password']);

        $this->actingAs($admin)->put(route('settings.profile'), [
            'name' => 'Admin KostKu',
            'email' => 'admin@kostku.test',
            'phone' => '081234567890',
        ])->assertRedirect(route('settings.edit', ['section' => 'profile']));

        $this->actingAs($admin)->put(route('settings.preferences'), [
            'locale' => 'id',
            'timezone' => 'Asia/Jakarta',
            'email_notifications' => true,
            'booking_notifications' => false,
            'payment_notifications' => true,
        ])->assertRedirect(route('settings.edit', ['section' => 'preferences']));

        $this->actingAs($admin)->put(route('settings.password'), [
            'current_password' => 'password',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
        ])->assertRedirect(route('settings.edit', ['section' => 'security']));

        $admin->refresh();
        $this->assertSame('Admin KostKu', $admin->name);
        $this->assertFalse($admin->preferences['booking_notifications']);
        $this->assertTrue(Hash::check('password-baru', $admin->password));
    }

    public function test_approving_booking_occupies_room_and_creates_tenant(): void
    {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->for(Kost::factory())->create(['status' => RoomStatus::Reserved]);
        $booking = Booking::factory()->for($room)->create(['status' => BookingStatus::Pending]);

        $this->actingAs($admin)->patch(route('bookings.approve', $booking))->assertSessionHas('success');

        $this->assertSame(BookingStatus::Approved, $booking->fresh()->status);
        $this->assertSame(RoomStatus::Occupied, $room->fresh()->status);
        $this->assertDatabaseHas('tenants', ['booking_id' => $booking->id, 'room_id' => $room->id]);
    }

    public function test_admin_can_verify_payment(): void
    {
        $admin = User::factory()->admin()->create();
        $payment = Payment::factory()->create(['status' => PaymentStatus::Pending]);
        $booking = $payment->booking;
        $booking->room->update(['status' => RoomStatus::Reserved]);

        $this->actingAs($admin)->patch(route('payments.approve', $payment))->assertSessionHas('success');

        $this->assertSame(PaymentStatus::Approved, $payment->fresh()->status);
        $this->assertSame($admin->id, $payment->fresh()->verified_by);
        $this->assertSame(BookingStatus::Approved, $booking->fresh()->status);
        $this->assertSame(RoomStatus::Occupied, $booking->room->fresh()->status);
        $this->assertDatabaseHas('tenants', ['booking_id' => $booking->id, 'room_id' => $booking->room_id]);
    }

    public function test_rejecting_booking_releases_reserved_room(): void
    {
        $admin = User::factory()->admin()->create();
        $room = Room::factory()->for(Kost::factory())->create(['status' => RoomStatus::Reserved]);
        $booking = Booking::factory()->for($room)->create(['status' => BookingStatus::Pending]);

        $this->actingAs($admin)->patch(route('bookings.reject', $booking))->assertSessionHas('success');

        $this->assertSame(BookingStatus::Rejected, $booking->fresh()->status);
        $this->assertSame(RoomStatus::Available, $room->fresh()->status);
    }
}
