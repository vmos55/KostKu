<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kost_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('room_number', 30);
            $table->unsignedSmallInteger('floor')->nullable();
            $table->decimal('price', 12, 2);
            $table->string('status', 20)->default('available')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['kost_id', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
