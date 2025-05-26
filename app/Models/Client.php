<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = ['full_name', 'email', 'gender', 'mac_address', 'device_type', 'platform', 'premium_expires_at', 'status', 'last_login_at', 'login_count', 'created_at', 'updated_at', 'language', 'data_usage', 'email_verified_at', 'remember_token'];


    //protected $hidden = ['remember_token'];

    protected $dates = ['premium_expires_at'];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}