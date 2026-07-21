<?php

namespace App\Models;

use App\Enums\KostStatus;
use Database\Factories\KostFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Kost extends Model
{
    /** @use HasFactory<KostFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'city',
        'description',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return ['status' => KostStatus::class];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
