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
        Schema::create('schedule_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->decimal('lat', 10, 7);
            $table->decimal('long', 10, 7);
            $table->string('location');
            $table->timestamps();

            // Foreign key reference to schedules table
            $table->foreign('schedule_id')->references('id')->on('schedules')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_locations');
    }
};