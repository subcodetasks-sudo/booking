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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_code')->unique();
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->unsignedSmallInteger('guest_count');
            $table->string('status')->default('pending');
            $table->string('order_status')->default('no_order');
            $table->foreignId('occasion_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('allergies_notes')->nullable();
            $table->text('reservation_notes')->nullable();
            $table->decimal('addons_total', 10, 2)->default(0);
            $table->decimal('items_total', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['reservation_date', 'reservation_time']);
            $table->index(['status', 'reservation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
