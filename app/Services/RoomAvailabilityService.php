<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Room;
use Carbon\CarbonInterface;

class RoomAvailabilityService
{
    /** @param  list<BookingStatus>  $statuses */
    public function hasConflict(
        Room $room,
        CarbonInterface $checkIn,
        ?CarbonInterface $checkOut,
        ?int $exceptBookingId = null,
        array $statuses = [BookingStatus::Pending, BookingStatus::Approved],
    ): bool {
        return $room->bookings()
            ->whereIn('status', $statuses)
            ->when($exceptBookingId, fn ($query) => $query->whereKeyNot($exceptBookingId))
            ->when($checkOut, fn ($query) => $query->whereDate('check_in_date', '<=', $checkOut))
            ->where(function ($query) use ($checkIn): void {
                $query->whereNull('check_out_date')
                    ->orWhereDate('check_out_date', '>=', $checkIn);
            })
            ->exists();
    }

    public function syncStatus(Room $room): void
    {
        $status = $room->bookings()->where('status', BookingStatus::Approved)->exists()
            ? RoomStatus::Occupied
            : ($room->bookings()->where('status', BookingStatus::Pending)->exists()
                ? RoomStatus::Reserved
                : RoomStatus::Available);

        if ($room->status !== $status) {
            $room->update(['status' => $status]);
        }
    }
}
