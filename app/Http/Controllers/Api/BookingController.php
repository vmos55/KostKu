<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Room;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $bookings = $request->user()->bookings()
            ->with(['room.kost', 'payments'])
            ->latest('booking_date')
            ->latest('id')
            ->get();

        return BookingResource::collection($bookings);
    }

    public function show(Request $request, Booking $booking): JsonResource
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        return new BookingResource($booking->load(['room.kost', 'payments']));
    }

    public function store(Request $request): JsonResource
    {
        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        $checkIn = CarbonImmutable::parse($data['check_in_date'])->startOfDay();
        $checkOut = $checkIn->addMonthsNoOverflow($data['duration_months'])->subDay();

        $booking = DB::transaction(function () use ($request, $data, $checkIn, $checkOut): Booking {
            $room = Room::with('kost')->lockForUpdate()->findOrFail($data['room_id']);

            if ($room->status !== RoomStatus::Available || $room->kost->status->value !== 'active') {
                throw ValidationException::withMessages([
                    'room_id' => ['Kamar ini sedang tidak tersedia.'],
                ]);
            }

            $hasConflict = $room->bookings()
                ->whereIn('status', [BookingStatus::Pending, BookingStatus::Approved])
                ->whereDate('check_in_date', '<=', $checkOut)
                ->where(function ($query) use ($checkIn): void {
                    $query->whereNull('check_out_date')
                        ->orWhereDate('check_out_date', '>=', $checkIn);
                })
                ->exists();

            if ($hasConflict) {
                throw ValidationException::withMessages([
                    'room_id' => ['Kamar sudah memiliki booking pada periode tersebut.'],
                ]);
            }

            $booking = Booking::create([
                'booking_code' => 'BK-'.now()->format('YmdHis').'-'.Str::upper(Str::random(4)),
                'user_id' => $request->user()->id,
                'room_id' => $room->id,
                'booking_date' => today(),
                'check_in_date' => $checkIn,
                'check_out_date' => $checkOut,
                'status' => BookingStatus::Pending,
            ]);

            $room->update(['status' => RoomStatus::Reserved]);

            return $booking;
        });

        return (new BookingResource($booking->load(['room.kost', 'payments'])))
            ->additional(['message' => 'Booking berhasil dibuat dan menunggu persetujuan admin.']);
    }
}
