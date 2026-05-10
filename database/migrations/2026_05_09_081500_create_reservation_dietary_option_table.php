<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_dietary_option', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dietary_option_id')->constrained()->cascadeOnDelete();
            $table->string('scope', 16)->default('self'); // self|guests
            $table->timestamps();

            $table->unique(['reservation_id', 'dietary_option_id'], 'res_diet_opt_unique');
            $table->index(['reservation_id', 'scope'], 'res_diet_scope_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_dietary_option');
    }
};

