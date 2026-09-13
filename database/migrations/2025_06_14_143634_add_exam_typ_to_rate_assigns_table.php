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
        Schema::table('rate_assigns', function (Blueprint $table) {
            $table->foreignId('exam_type_id')
                ->after('session_id')
                ->constrained('exam_types')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_assigns', function (Blueprint $table) {

            Schema::dropIfExists('rate_assigns');
        });
    }
};
