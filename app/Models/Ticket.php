<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'destination_id',
        'vehicle_id',
        'accommodation_id',
        'ticket_code',
        'expired_at',
        'guest_count',
        'total_price',
        'price_breakdown',
    ];

    protected $casts = [
        'price_breakdown' => 'array',
        'expired_at' => 'datetime',
    ];

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }
}
