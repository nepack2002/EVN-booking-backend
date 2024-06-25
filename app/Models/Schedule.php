<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'datetime',
        'location',
        'lat_location',
        'long_location',
        'location_2',
        'lat_location_2',
        'long_location_2',
        'car_id',
        'participants',
        'program',
        'tai_lieu',
        'ten_tai_lieu',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id');
    }
}
