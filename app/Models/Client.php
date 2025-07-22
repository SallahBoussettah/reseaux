<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Client extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'full_name', 
        'email', 
        'gender',
        'mac_address', 
        'device_type', 
        'platform', 
        'premium_expires_at', 
        'status', 
        'profile_type',
        'scheduled_deletion_at',
        'last_login_at', 
        'login_count', 
        'total_downloaded_bytes',
        'total_uploaded_bytes',
        'created_at', 
        'updated_at', 
        'language', 
        'email_verified_at', 
        'remember_token',
        'verification_token',
        'verification_token_expires_at',
        'verification_token_attempts',
        'successful_verifications'
    ];


    //protected $hidden = ['remember_token'];

    protected $dates = [
        'premium_expires_at',
        'verification_token_expires_at',
        'scheduled_deletion_at'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'verification_token_expires_at' => 'datetime',
        'scheduled_deletion_at' => 'datetime',
    ];

    /**
     * Get the feedback submitted by the client
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class);
    }
}