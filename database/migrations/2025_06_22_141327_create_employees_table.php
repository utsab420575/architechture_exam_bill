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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->string('employeename');
            $table->string('phoneno')->nullable();
            $table->string('photo')->nullable();
            $table->string('preaddress')->nullable();
            $table->string('peraddress')->nullable();

            $table->unsignedBigInteger('designation_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('user_id');


            // Foreign keys
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
