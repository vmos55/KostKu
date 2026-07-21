<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KostResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $rooms = $this->whenLoaded('rooms');
        $minimumPrice = $this->relationLoaded('rooms') && $this->rooms->isNotEmpty()
            ? (int) $this->rooms->min('price')
            : null;

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address,
            'city' => $this->city,
            'location' => collect([$this->address, $this->city])->filter()->implode(', '),
            'description' => $this->description,
            'image_url' => $this->image ? url('storage/'.$this->image) : null,
            'status' => $this->status->value,
            'price' => $minimumPrice,
            'rooms' => RoomResource::collection($rooms),
        ];
    }
}
