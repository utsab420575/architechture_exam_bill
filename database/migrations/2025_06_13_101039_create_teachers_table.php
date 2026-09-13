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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teachername');
            $table->string('specialization')->nullable();
            $table->string('availability')->nullable();
            $table->string('phoneno')->nullable();
            $table->string('photo')->nullable();
            $table->string('preaddress')->nullable();
            $table->string('peraddress')->nullable();
            $table->foreignId('designation_id')->constrained('designations')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('department_id')->constrained('departments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onUpdate('restrict')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
