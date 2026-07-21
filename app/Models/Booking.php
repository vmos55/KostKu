<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'booking_code', 'user_id', 'room_id', 'booking_date', 'check_in_date',
        'check_out_date', 'status', 'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'status' => BookingStatus::class,
            'reviewed_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function tenant(): HasOne
    {
        return $this->hasOne(Tenant::class);
    }
}
