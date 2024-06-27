<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarHistory extends Model {
    protected $table = 'car_histories';

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'submit_by');
    }

    public function car()
    {
        return $this->hasOne(Car::class, 'id', 'car_id');
    }
}
