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
        Schema::create('reservation_reservation_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_addon_id')->constrained()->cascadeOnDelete();
            $table->string('addon_name');
            $table->decimal('addon_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->timestamps();

            $table->unique(['reservation_id', 'reservation_addon_id'], 'reservation_addon_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_reservation_addon');
    }
};
