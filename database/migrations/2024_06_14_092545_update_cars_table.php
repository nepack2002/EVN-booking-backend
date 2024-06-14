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
            $table->string('location');
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
