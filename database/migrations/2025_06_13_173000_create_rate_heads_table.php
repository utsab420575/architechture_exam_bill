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
        Schema::create('rate_heads', function (Blueprint $table) {
            $table->id();
            $table->string('head');
            $table->string('sub_head')->nullable();
            $table->foreignId('exam_type')->constrained('exam_types')->onDelete('cascade');
            $table->string('order_no');
            $table->string('dist_type'); // individual or share
            $table->boolean('enable_min')->default(false);
            $table->boolean('enable_max')->default(false);
            $table->boolean('is_course')->default(false);
            $table->boolean('is_student_count')->default(false);
            $table->foreignId('marge_with')->nullable()->constrained('rate_heads')->onDelete('set null');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_heads');
    }
};
