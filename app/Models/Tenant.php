<?php

namespace App\Models;

use App\Enums\IdentityVerificationStatus;
use App\Enums\TenantStatus;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id', 'room_id', 'booking_id', 'identity_number',
        'identity_verification_status', 'check_in', 'check_out', 'status',
    ];

    protected function casts(): array
    {
        return [
            'identity_verification_status' => IdentityVerificationStatus::class,
            'check_in' => 'date',
            'check_out' => 'date',
            'status' => TenantStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
