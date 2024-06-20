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
        //
        Schema::table('cars', function (Blueprint $table) {
           $table->decimal('lat_location', 10, 7)->nullable()->change();
           $table->decimal('long_location', 10, 7)->nullable()->change();
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->decimal('lat_location', 10, 7)->nullable()->change();
            $table->decimal('long_location', 10, 7)->nullable()->change();
            $table->decimal('lat_location_2', 10, 7)->nullable()->change();
            $table->decimal('long_location_2', 10, 7)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
