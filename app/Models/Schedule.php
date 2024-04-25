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
        'car_id',
        'participants',
        'program'
    ];
    public function department()
    {
        return $this->belongsTo(Department::class,'dependent_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class,'car_id');
    }
}