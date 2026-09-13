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
        Schema::table('rate_amounts', function (Blueprint $table) {
            $table->unique(['session_id', 'exam_type_id', 'rate_head_id'], 'unique_session_examtype_ratehead');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rate_amounts', function (Blueprint $table) {
            $table->dropUnique('unique_session_examtype_ratehead');
        });
    }
};
