<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\IdentityVerificationStatus;
use App\Enums\TenantStatus;
use App\Models\Booking;
use App\Services\RoomAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['user', 'room.kost'])
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('booking_code', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status))
            ->latest('booking_date')->paginate(10)->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function show(Booking $booking): View
    {
        $booking->load(['user', 'room.kost', 'payments', 'tenant']);

        return view('bookings.show', compact('booking'));
    }

    public function approve(Request $request, Booking $booking, RoomAvailabilityService $availability): RedirectResponse
    {
        DB::transaction(function () use ($request, $booking, $availability): void {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            $room = $booking->room()->lockForUpdate()->firstOrFail();

            if ($booking->status !== BookingStatus::Pending
                || $availability->hasConflict(
                    $room,
                    $booking->check_in_date,
                    $booking->check_out_date,
                    $booking->id,
                    [BookingStatus::Approved],
                )) {
                throw ValidationException::withMessages(['booking' => 'Booking tidak dapat disetujui karena kamar sudah terisi atau status telah berubah.']);
            }

            $booking->update([
                'status' => BookingStatus::Approved,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => null,
            ]);
            $availability->syncStatus($room);
            $booking->tenant()->create([
                'user_id' => $booking->user_id,
                'room_id' => $booking->room_id,
                'identity_verification_status' => IdentityVerificationStatus::Pending,
                'check_in' => $booking->check_in_date,
                'check_out' => $booking->check_out_date,
                'status' => TenantStatus::Active,
            ]);
        });

        return back()->with('success', 'Booking disetujui dan kamar ditandai terisi.');
    }

    public function reject(Request $request, Booking $booking, RoomAvailabilityService $availability): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => ['nullable', 'string', 'max:1000']]);

        DB::transaction(function () use ($request, $booking, $data, $availability): void {
            $booking = Booking::lockForUpdate()->findOrFail($booking->id);
            $room = $booking->room()->lockForUpdate()->firstOrFail();

            if ($booking->status !== BookingStatus::Pending) {
                throw ValidationException::withMessages(['booking' => 'Hanya booking pending yang dapat ditolak.']);
            }

            $booking->update([
                'status' => BookingStatus::Rejected,
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'rejection_reason' => $data['rejection_reason'] ?? null,
            ]);

            $availability->syncStatus($room);
        });

        return back()->with('success', 'Booking berhasil ditolak.');
    }
}
