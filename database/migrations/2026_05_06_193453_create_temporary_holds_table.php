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
        Schema::create('temporary_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('time_slot_id')->constrained()->cascadeOnDelete();
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->unsignedSmallInteger('guest_count');
            $table->string('session_key')->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_holds');
    }
};
