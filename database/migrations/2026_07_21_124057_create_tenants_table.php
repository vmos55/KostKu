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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('room_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('booking_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identity_number', 30)->nullable()->unique();
            $table->string('identity_verification_status', 20)->default('pending')->index();
            $table->date('check_in');
            $table->date('check_out')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->timestamps();

            $table->index(['room_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
