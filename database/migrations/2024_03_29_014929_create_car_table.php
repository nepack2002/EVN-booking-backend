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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('ten_xe');
            $table->string('mau_xe');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('bien_so_xe')->unique();
            $table->string('so_khung')->unique();
            $table->integer('so_cho');
            $table->enum('dac_diem_mac_dinh', ['A', 'B'])->nullable();
            $table->float('so_dau_xang_tieu_thu');
            $table->date('ngay_bao_duong_gan_nhat')->nullable();
            $table->date('han_dang_kiem_tiep_theo')->nullable();
            $table->string('anh_xe')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};