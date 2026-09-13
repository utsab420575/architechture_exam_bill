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
        Schema::table('sessions', function (Blueprint $table) {
            // First, drop the existing enum column
            $table->dropColumn('exam_type');

            // Then add the foreign key column
            $table->foreignId('exam_type')
                ->constrained('exam_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {

            // Drop the foreign key and column
            $table->dropForeign(['exam_type_id']);
            $table->dropColumn('exam_type_id');

            // Restore the old enum column
            $table->enum('exam_type', ['Regular', 'Review'])->default('Regular');
        });
    }
};
