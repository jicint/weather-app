<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'travel_id',
        'user_id',
        'booking_type', // accommodation, transportation, activity
        'provider',
        'booking_reference',
        'start_date',
        'end_date',
        'status',
        'cost',
        'details'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'details' => 'array'
    ];

    public function travel()
    {
        return $this->belongsTo(Travel::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 