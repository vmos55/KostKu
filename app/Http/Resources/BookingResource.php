<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $duration = $this->check_out_date
            ? max(1, (int) $this->check_in_date->diffInMonths($this->check_out_date->copy()->addDay()))
            : 1;

        return [
            'id' => $this->id,
            'booking_code' => $this->booking_code,
            'booking_date' => $this->booking_date->toDateString(),
            'check_in_date' => $this->check_in_date->toDateString(),
            'check_out_date' => $this->check_out_date?->toDateString(),
            'duration_months' => $duration,
            'status' => $this->status->value,
            'rejection_reason' => $this->rejection_reason,
            'total' => $this->relationLoaded('room') ? (int) $this->room->price * $duration : null,
            'room' => new RoomResource($this->whenLoaded('room')),
            'kost' => $this->when(
                $this->relationLoaded('room') && $this->room->relationLoaded('kost'),
                fn () => [
                    'id' => $this->room->kost->id,
                    'name' => $this->room->kost->name,
                    'location' => collect([$this->room->kost->address, $this->room->kost->city])->filter()->implode(', '),
                    'image_url' => $this->room->kost->image ? url('storage/'.$this->room->kost->image) : null,
                ],
            ),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
        ];
    }
}
