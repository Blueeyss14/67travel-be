<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'destination_id',
        'accommodation_id',
        'vehicle_id',
        'ticket_code',
        'expired_at',
        'guest_count',
        'total_price',
        'price_breakdown'
    ];

    protected $casts = [
        'price_breakdown' => 'array',
        'expired_at' => 'datetime'
    ];
}
