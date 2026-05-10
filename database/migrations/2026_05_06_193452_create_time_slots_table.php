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
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->date('slot_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('capacity');
            $table->unsignedSmallInteger('reserved_guests')->default(0);
            $table->unsignedSmallInteger('held_guests')->default(0);
            $table->boolean('is_closed_manually')->default(false);
            $table->timestamps();

            $table->unique(['slot_date', 'start_time']);
            $table->index(['slot_date', 'is_closed_manually']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
