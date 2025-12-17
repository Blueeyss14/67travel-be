<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'location',
        'owner',
        // 'numberOfGuest',
        'maxOfGuest',
        'rating',
        'ratings',
        'price',
        'thumbnailUrl',
        'facilities',
        'imageUrls',
        'description'
    ];

    protected $casts = [
        'facilities' => 'array',
        'imageUrls' => 'array',
        'ratings' => 'array',
    ];
}
