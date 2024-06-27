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
        Schema::create('car_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('car_id')->index('car_id');
            $table->bigInteger('submit_by');
            $table->date('ngay_bao_duong_gan_nhat')->nullable();
            $table->date('han_dang_kiem_tiep_theo')->nullable();
            $table->string('tai_lieu')->nullable();
            $table->enum('trang_thai', ['0', '1'])->default('1');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_histories');
    }
};
