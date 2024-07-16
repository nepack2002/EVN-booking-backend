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
            $table->dropColumn('dac_diem_mac_dinh');
            $table->text('location');
            $table->decimal('lat_location', 10, 7)->nullable()->change();
            $table->decimal('long_location', 10, 7)->nullable()->change();
        });
        Schema::table('schedule_locations', function (Blueprint $table) {
            $table->text('location')->nullable()->change();

        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->text('participants')->change();
            $table->text('location')->nullable()->change();
            $table->text('location_2')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('cars', function (Blueprint $table) {
            $table->enum('dac_diem_mac_dinh', ['A', 'B'])->nullable();
            $table->dropColumn('location');
            
        });
    }
};