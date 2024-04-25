<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department_id'
        
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function car()
    {
        return $this->hasMany(Car::class, 'user_id');
    }
    public function department(){
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isQTVT()
    {
        return $this->role === 'quản trị vật tư';
    }
    public function isQTCT()
    {
        return $this->role === 'quản trị công tác';
    }

}