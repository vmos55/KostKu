<?php

namespace App\Http\Controllers;

use App\Enums\PaymentStatus;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        if ($payment->status !== PaymentStatus::Pending) {
            return back()->with('error', 'Pembayaran ini sudah diverifikasi.');
        }

        $payment->update([
            'status' => PaymentStatus::Approved,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Pembayaran berhasil disetujui.');
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
