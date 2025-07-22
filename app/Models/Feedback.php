<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'email',
        'wifi_rating',
        'hotel_rating',
        'room_rating',
        'service_rating',
        'food_rating',
        'is_satisfied',
        'visit_again',
        'comments',
        'suggestion',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_satisfied' => 'boolean',
        'visit_again' => 'boolean',
        'wifi_rating' => 'integer',
        'hotel_rating' => 'integer',
        'room_rating' => 'integer',
        'service_rating' => 'integer',
        'food_rating' => 'integer',
    ];

    /**
     * Get the client that submitted this feedback.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
} 