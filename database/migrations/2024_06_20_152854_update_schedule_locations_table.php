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
        Schema::table('schedule_locations', function (Blueprint $table) {
            $table->double('so_dau_xang_tieu_thu', 8, 7)->nullable()->change();
        });
        Schema::table('schedules', function (Blueprint $table) {
            $table->text('participants')->change();
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
