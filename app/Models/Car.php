<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;
    protected $fillable = ['ten_xe', 'mau_xe', 'user_id', 'bien_so_xe', 'so_khung', 'so_cho', 'dac_diem_mac_dinh', 'so_dau_xang_tieu_thu', 'ngay_bao_duong_gan_nhat', 'han_dang_kiem_tiep_theo', 'anh_xe'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}