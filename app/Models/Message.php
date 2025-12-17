<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'userId',
        'userName',
        'user_profile_photo',
        'userMessage',
        'adminMessage',
        'timestamp'
    ];

    public $timestamps = false;

    protected $casts = [
        'timestamp' => 'datetime:Y-m-d H:i:s',
    ];

}
