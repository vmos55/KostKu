<?php

namespace App\Models;

use App\Enums\RoomStatus;
use App\Enums\TenantStatus;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = ['kost_id', 'room_number', 'floor', 'price', 'status'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'status' => RoomStatus::class];
    }

    public function kost(): BelongsTo
    {
        return $this->belongsTo(Kost::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function currentTenant(): HasOne
    {
        return $this->hasOne(Tenant::class)
            ->where('status', TenantStatus::Active->value)
            ->latestOfMany('check_in');
    }
}
