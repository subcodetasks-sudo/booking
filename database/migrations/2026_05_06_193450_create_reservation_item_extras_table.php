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
        Schema::create('reservation_item_extras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reservation_item_id');
            $table->unsignedBigInteger('product_extra_id')->nullable();
            $table->string('extra_name');
            $table->decimal('extra_price', 10, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('line_total', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservation_item_extras');
    }
};
