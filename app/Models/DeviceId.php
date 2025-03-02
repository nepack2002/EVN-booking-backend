<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceId extends Model
{
    use HasFactory;

    protected $fillable = ['onesignal_id', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}