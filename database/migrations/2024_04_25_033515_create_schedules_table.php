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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->date('datetime');
            $table->string('location');
            $table->float('lat_location')->nullable();
            $table->float('long_location')->nullable();     
            $table->unsignedBigInteger('car_id');
            $table->string('participants');
            $table->string('program');
            $table->timestamps();
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('car_id')->references('id')->on('cars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};