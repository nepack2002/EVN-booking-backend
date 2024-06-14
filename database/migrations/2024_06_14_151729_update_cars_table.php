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
           $table->date('ngay_sua_chua_lon_gan_nhat')->nullable();
           $table->string('so_may');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('cars', function (Blueprint $table) {
            $table->dropColumn('ngay_sua_chua_lon_gan_nhat');
            $table->dropColumn('so_may');
        });
    }
};
