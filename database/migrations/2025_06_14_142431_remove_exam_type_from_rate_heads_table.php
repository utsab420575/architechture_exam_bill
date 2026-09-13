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
        Schema::table('rate_heads', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['exam_type']);
            // Then drop the column
            $table->dropColumn('exam_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_heads', function (Blueprint $table) {
            $table->foreignId('exam_type')
                ->constrained('exam_types')
                ->onDelete('cascade');
        });
    }
};
