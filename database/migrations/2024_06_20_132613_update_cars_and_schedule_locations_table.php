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
            $table->tinyInteger('theo_doi_vi_tri')->default(1);
        });
        Schema::table('schedule_locations', function (Blueprint $table) {
            $table->float('so_dau_xang_tieu_thu')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('theo_doi_vi_tri');
        });
        Schema::table('schedule_locations', function (Blueprint $table) {
            $table->dropColumn('so_dau_xang_tieu_thu');
        });
    }
};
