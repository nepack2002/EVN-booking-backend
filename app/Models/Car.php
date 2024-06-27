<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = ['ten_xe', 'mau_xe', 'user_id', 'bien_so_xe', 'so_khung', 'so_cho', 'location', 'so_dau_xang_tieu_thu', 'ngay_bao_duong_gan_nhat', 'han_dang_kiem_tiep_theo', 'anh_xe', 'lat_location', 'long_location', 'so_may', 'theo_doi_vi_tri', 'ngay_sua_chua_lon_gan_nhat'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function schedule()
    {
        return $this->hasMany(Schedule::class, 'car_id');
    }

    public function history()
    {
        return $this->hasMany(CarHistory::class, 'car_id');
    }
}
