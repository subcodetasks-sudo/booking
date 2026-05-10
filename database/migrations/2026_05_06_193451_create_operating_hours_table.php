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
        Schema::create('operating_hours', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('open_time');
            $table->time('close_time');
            $table->unsignedSmallInteger('slot_duration_minutes')->default(30);
            $table->unsignedSmallInteger('capacity_per_slot')->default(0);
            $table->boolean('is_open')->default(true);
            $table->timestamps();

            $table->unique('day_of_week');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_hours');
    }
};
