<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\IdentityVerificationStatus;
use App\Enums\PaymentStatus;
use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $payments = Payment::with(['booking.user', 'booking.room.kost'])
            ->when($request->string('search')->toString(), fn ($query, $search) => $query->where(fn ($inner) => $inner
                ->where('transaction_code', 'like', "%{$search}%")
                ->orWhereHas('booking.user', fn ($user) => $user->where('name', 'like', "%{$search}%"))))
            ->when($request->string('status')->toString(), fn ($query, $status) => $query->where('status', $status), fn ($query) => $query->where('status', PaymentStatus::Pending))
            ->latest('submitted_at')->paginate(10)->withQueryString();

        $selectedPayment = $request->integer('payment')
            ? Payment::with(['booking.user', 'booking.room.kost'])->find($request->integer('payment'))
            : $payments->first();

        return view('payments.index', compact('payments', 'selectedPayment'));
    }

    public function approve(Request $request, Payment $payment): RedirectResponse
    {
        DB::transaction(function () use ($request, $payment): void {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);

            if ($payment->status !== PaymentStatus::Pending) {
                throw ValidationException::withMessages(['payment' => 'Pembayaran ini sudah diverifikasi.']);
            }

            $booking = $payment->booking()->lockForUpdate()->firstOrFail();
            $room = $booking->room()->lockForUpdate()->firstOrFail();

            if ($booking->status === BookingStatus::Rejected) {
                throw ValidationException::withMessages(['booking' => 'Pembayaran tidak dapat disetujui karena booking telah ditolak.']);
            }

            if ($booking->status === BookingStatus::Pending) {
                if (! in_array($room->status, [RoomStatus::Available, RoomStatus::Reserved], true)) {
                    throw ValidationException::withMessages(['booking' => 'Booking tidak dapat disetujui karena kamar sudah terisi.']);
                }

                $booking->update([
                    'status' => BookingStatus::Approved,
                    'reviewed_by' => $request->user()->id,
                    'reviewed_at' => now(),
                    'rejection_reason' => null,
                ]);
                $room->update(['status' => RoomStatus::Occupied]);
                $booking->tenant()->firstOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'user_id' => $booking->user_id,
                        'room_id' => $booking->room_id,
                        'identity_verification_status' => IdentityVerificationStatus::Pending,
                        'check_in' => $booking->check_in_date,
                        'check_out' => $booking->check_out_date,
                        'status' => TenantStatus::Active,
                    ],
                );
            }

            $payment->update([
                'status' => PaymentStatus::Approved,
                'verified_by' => $request->user()->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);
        });

        return back()->with('success', 'Pembayaran dan booking berhasil disetujui. Kamar kini ditandai terisi.');
    }

    public function reject(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate(['rejection_reason' => ['nullable', 'string', 'max:1000']]);

        if ($payment->status !== PaymentStatus::Pending) {
            return back()->with('error', 'Pembayaran ini sudah diverifikasi.');
        }

        $payment->update([
            'status' => PaymentStatus::Rejected,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_reason' => $data['rejection_reason'] ?? null,
        ]);

        return back()->with('success', 'Pembayaran berhasil ditolak.');
    }
}
