<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\IdentityVerificationStatus;
use App\Enums\KostStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Kost;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@kostku.com'], [
            'name' => 'Admin User', 'phone' => '081234567890',
            'role' => UserRole::Admin, 'password' => 'password',
        ]);

        if (Kost::exists()) {
            return;
        }

        $kosts = collect([
            ['code' => 'KST-001', 'name' => 'Kost Mentari', 'city' => 'Jakarta Selatan', 'address' => 'Jl. Mentari Raya No. 18, Jakarta Selatan'],
            ['code' => 'KST-002', 'name' => 'Kost Indah', 'city' => 'Bandung Barat', 'address' => 'Jl. Melati Indah No. 7, Bandung Barat'],
            ['code' => 'KST-003', 'name' => 'Kost Exclusive 88', 'city' => 'Surabaya Pusat', 'address' => 'Jl. Pemuda No. 88, Surabaya'],
        ])->map(fn (array $data) => Kost::create($data + [
            'description' => 'Hunian nyaman, aman, dan strategis dengan fasilitas lengkap.',
            'status' => KostStatus::Active,
        ]));

        $rooms = collect();
        foreach ($kosts as $index => $kost) {
            foreach (range(1, 4) as $number) {
                $rooms->push(Room::create([
                    'kost_id' => $kost->id,
                    'room_number' => chr(65 + $index).'-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'floor' => $number <= 2 ? 1 : 2,
                    'price' => 1500000 + ($index * 500000),
                    'status' => $number <= 2 ? RoomStatus::Occupied : RoomStatus::Available,
                ]));
            }
        }

        $names = ['Budi Santoso', 'Siti Aminah', 'Andi Wijaya', 'Rina Kartika', 'Citra Dewi', 'Dodi Wahyudi'];
        foreach ($names as $index => $name) {
            $user = User::create([
                'name' => $name,
                'email' => 'tenant'.($index + 1).'@kostku.test',
                'phone' => '0856'.str_pad((string) (78901230 + $index), 8, '0', STR_PAD_LEFT),
                'role' => UserRole::Tenant,
                'password' => 'password',
            ]);
            $room = $rooms->filter(fn (Room $room) => $room->status === RoomStatus::Occupied)->values()->get($index);
            $booking = Booking::create([
                'booking_code' => 'BK-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'room_id' => $room->id,
                'booking_date' => now()->subMonths(5 - $index)->subDays(4),
                'check_in_date' => now()->subMonths(5 - $index),
                'status' => BookingStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now()->subMonths(5 - $index)->subDays(2),
            ]);
            Tenant::create([
                'user_id' => $user->id, 'room_id' => $room->id, 'booking_id' => $booking->id,
                'identity_number' => '327301'.str_pad((string) ($index + 1), 10, '0', STR_PAD_LEFT),
                'identity_verification_status' => $index === 4 ? IdentityVerificationStatus::Pending : IdentityVerificationStatus::Verified,
                'check_in' => $booking->check_in_date, 'status' => TenantStatus::Active,
            ]);
            Payment::create([
                'transaction_code' => 'TRX-'.(8923 + $index), 'booking_id' => $booking->id,
                'amount' => $room->price, 'proof_image' => 'payments/proofs/demo-'.($index + 1).'.jpg',
                'status' => $index >= 4 ? PaymentStatus::Pending : PaymentStatus::Approved,
                'submitted_at' => now()->subMonths(5 - $index),
                'verified_by' => $index >= 4 ? null : $admin->id,
                'verified_at' => $index >= 4 ? null : now()->subMonths(5 - $index)->addDay(),
            ]);
        }

        $currentBooking = Booking::oldest('id')->firstOrFail();
        Payment::create([
            'transaction_code' => 'TRX-9001', 'booking_id' => $currentBooking->id,
            'amount' => $currentBooking->room->price, 'proof_image' => 'payments/proofs/demo-current.jpg',
            'status' => PaymentStatus::Approved, 'submitted_at' => now()->subDay(),
            'verified_by' => $admin->id, 'verified_at' => now(),
        ]);

        foreach (range(1, 3) as $index) {
            $user = User::create([
                'name' => ['Andi Rahman', 'Siti Wahyuni', 'Dewi Ratna'][$index - 1],
                'email' => 'calon'.$index.'@kostku.test', 'role' => UserRole::Tenant, 'password' => 'password',
            ]);
            Booking::create([
                'booking_code' => 'BK-PENDING-00'.$index, 'user_id' => $user->id,
                'room_id' => $rooms->filter(fn (Room $room) => $room->status === RoomStatus::Available)->values()->get($index - 1)->id,
                'booking_date' => now()->subDays($index), 'check_in_date' => now()->addDays(7 + $index),
                'status' => BookingStatus::Pending,
            ]);
        }
    }
}
