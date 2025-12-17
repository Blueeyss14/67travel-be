<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'name',
        'location',
        'owner',
        'maxOfGuest',
        'price',
        'thumbnailUrl',
        'facilities',
        'description',
        'imageUrls',
        'admin_id',
        'ratings',
        'rating',
        'user_bookmarks'
    ];

    protected $casts = [
        'facilities' => 'array',
        'imageUrls' => 'array',
        'ratings' => 'array',
        'user_bookmarks' => 'array',
    ];
}
