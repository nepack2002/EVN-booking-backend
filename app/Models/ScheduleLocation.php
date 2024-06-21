<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'location',
        'lat_location',
        'long_location',
        'so_dau_xang_tieu_thu'
    ];
}
