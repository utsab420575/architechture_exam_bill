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
        Schema::create('rate_amounts', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_rate', 10, 2);
            $table->decimal('min_rate', 10, 2)->nullable();
            $table->decimal('max_rate', 10, 2)->nullable();
            $table->boolean('saved')->default(false);
            $table->foreignId('session_id')->constrained('sessions')->onDelete('cascade');
            $table->foreignId('rate_head_id')->constrained('rate_heads')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_amounts');
    }
};
