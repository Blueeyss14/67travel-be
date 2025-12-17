<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accommodation extends Model
{
    protected $fillable = [
        'admin_id',
        'name',
        'latitude',
        'longitude',
        'price',
        'thumbnail',
    ];
}
