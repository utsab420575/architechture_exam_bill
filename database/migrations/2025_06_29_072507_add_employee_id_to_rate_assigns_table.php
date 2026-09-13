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
            // Drop foreign key before altering column
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('rate_assigns', function (Blueprint $table) {
            // Make teacher_id nullable
            $table->unsignedBigInteger('teacher_id')->nullable()->change();

            // Add employee_id
            $table->unsignedBigInteger('employee_id')->nullable()->after('teacher_id');

            // Re-add foreign keys
            $table->foreign('teacher_id')
                ->references('id')->on('teachers')
                ->onDelete('cascade')
                ->onUpdate('restrict');

            $table->foreign('employee_id')
                ->references('id')->on('employees')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('rate_assigns', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('rate_assigns', function (Blueprint $table) {
            // Drop column
            $table->dropColumn('employee_id');

            // Make teacher_id NOT NULL again
            $table->unsignedBigInteger('teacher_id')->nullable(false)->change();

            // Recreate foreign key
            $table->foreign('teacher_id')
                ->references('id')->on('teachers')
                ->onDelete('cascade')
                ->onUpdate('restrict');
        });
    }
};
