<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function store(Request $request, Booking $booking): JsonResource
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        $request->validate([
            'proof_image' => ['required', File::image()->max('5mb')],
        ]);

        if ($booking->payments()->whereIn('status', [PaymentStatus::Pending, PaymentStatus::Approved])->exists()) {
            throw ValidationException::withMessages([
                'proof_image' => ['Bukti pembayaran aktif sudah pernah dikirim.'],
            ]);
        }

        $booking->loadMissing('room');
        $duration = max(1, (int) $booking->check_in_date->diffInMonths($booking->check_out_date->copy()->addDay()));
        $path = $request->file('proof_image')->store('payments/proofs', 'public');

        $payment = Payment::create([
            'transaction_code' => 'TRX-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
            'booking_id' => $booking->id,
            'amount' => (float) $booking->room->price * $duration,
            'proof_image' => $path,
            'status' => PaymentStatus::Pending,
            'submitted_at' => now(),
        ]);

        return (new PaymentResource($payment))
            ->additional(['message' => 'Bukti pembayaran berhasil dikirim.']);
    }
}
