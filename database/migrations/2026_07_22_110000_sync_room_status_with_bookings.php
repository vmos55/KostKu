<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('rooms')
            ->where('status', 'available')
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('bookings')
                ->whereColumn('bookings.room_id', 'rooms.id')
                ->where('bookings.status', 'approved'))
            ->update(['status' => 'occupied']);

        DB::table('rooms')
            ->where('status', 'available')
            ->whereExists(fn ($query) => $query
                ->selectRaw('1')
                ->from('bookings')
                ->whereColumn('bookings.room_id', 'rooms.id')
                ->where('bookings.status', 'pending'))
            ->update(['status' => 'reserved']);
    }

    public function down(): void
    {
        DB::table('rooms')->where('status', 'reserved')->update(['status' => 'available']);
    }
};
