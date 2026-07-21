<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'transaction_code' => $this->transaction_code,
            'booking_id' => $this->booking_id,
            'amount' => (int) $this->amount,
            'proof_image_url' => url('storage/'.$this->proof_image),
            'status' => $this->status->value,
            'submitted_at' => $this->submitted_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'rejection_reason' => $this->rejection_reason,
        ];
    }
}
