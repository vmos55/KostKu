<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Room;
use App\Services\RoomAvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
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

    public function availability(Request $request, Room $room, RoomAvailabilityService $availability): JsonResponse
    {
        $data = $request->validate([
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
        ]);
        $checkIn = CarbonImmutable::parse($data['check_in_date'])->startOfDay();
        $checkOut = $checkIn->addMonthsNoOverflow((int) $data['duration_months'])->subDay();
        $isAvailable = $room->kost->status->value === 'active'
            && ! $availability->hasConflict($room, $checkIn, $checkOut);

        return response()->json([
            'available' => $isAvailable,
            'check_in_date' => $checkIn->toDateString(),
            'check_out_date' => $checkOut->toDateString(),
            'message' => $isAvailable
                ? 'Kamar tersedia pada periode yang dipilih.'
                : 'Kamar sudah dipesan pada sebagian atau seluruh periode tersebut.',
        ]);
    }

    public function store(Request $request, RoomAvailabilityService $availability): JsonResource
    {
        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date', 'after_or_equal:today'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
        ]);

        $checkIn = CarbonImmutable::parse($data['check_in_date'])->startOfDay();
        $checkOut = $checkIn->addMonthsNoOverflow((int) $data['duration_months'])->subDay();

        $booking = DB::transaction(function () use ($request, $data, $checkIn, $checkOut, $availability): Booking {
            $room = Room::with('kost')->lockForUpdate()->findOrFail($data['room_id']);

            if ($room->kost->status->value !== 'active') {
                throw ValidationException::withMessages([
                    'room_id' => ['Kamar ini sedang tidak tersedia.'],
                ]);
            }

            if ($availability->hasConflict($room, $checkIn, $checkOut)) {
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

            $availability->syncStatus($room);

            return $booking;
        });

        return (new BookingResource($booking->load(['room.kost', 'payments'])))
            ->additional(['message' => 'Booking berhasil dibuat dan menunggu persetujuan admin.']);
    }
}
