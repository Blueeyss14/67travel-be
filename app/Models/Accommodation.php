<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'latitude',
        'longitude',
        'price',
        'thumbnail',
    ];
}
