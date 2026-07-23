<?php

namespace App\Http\Resources;

use App\Enums\RoomStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kost_id' => $this->kost_id,
            'number' => $this->room_number,
            'room_number' => $this->room_number,
            'floor' => $this->floor,
            'price' => (int) $this->price,
            'status' => $this->status->value,
            'available' => in_array($this->status, [RoomStatus::Available, RoomStatus::Reserved], true),
        ];
    }
}
