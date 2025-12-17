<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    protected $fillable = [
    'admin_id',
    'name',
    'price',
    'maxPassenger',
    'thumbnailUrl'
];

}
