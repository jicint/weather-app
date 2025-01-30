<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Travel extends Model
{
    use HasFactory;

    protected $table = 'travels';

    protected $fillable = [
        'user_id',
        'source',
        'destination',
        'travel_date',
        'return_date',
        'family_size',
        'has_children',
        'weather_conditions',
        'recommendations',
        'is_favorite'
    ];

    protected $casts = [
        'travel_date' => 'datetime',
        'return_date' => 'datetime',
        'has_children' => 'boolean',
        'weather_conditions' => 'array',
        'recommendations' => 'array',
        'is_favorite' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
} 