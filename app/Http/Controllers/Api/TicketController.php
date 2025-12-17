<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\Destination;
use App\Models\Accommodation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
public function store(Request $request)
{
    $request->validate([
        'destination_id' => 'nullable|exists:destinations,id',
        'accommodation_id' => 'nullable|exists:accommodations,id',
        'vehicle_id' => 'nullable|exists:vehicles,id',
        'expired_at' => 'required|date',
        'guest_count' => 'nullable|integer|min:1', // baru ditambah
    ]);

    $user = auth()->user();

    $destination = $request->destination_id
        ? Destination::findOrFail($request->destination_id)
        : null;

    $accommodation = $request->accommodation_id
        ? Accommodation::findOrFail($request->accommodation_id)
        : null;

    $vehicle = $request->vehicle_id
        ? Vehicle::findOrFail($request->vehicle_id)
        : null;

    $breakdown = [];
    $total = 0;
    $guestCount = $request->guest_count ?? ($destination?->numberOfGuest ?? 1);

    if ($destination) {
        $subtotal = $destination->price * $guestCount;
        $breakdown[] = [
            'type' => 'destination',
            'name' => $destination->name,
            'detail_price' => "{$guestCount} x {$destination->price}",
            'total' => $subtotal
        ];
        $total += $subtotal;
    }

    if ($vehicle) {
        $breakdown[] = [
            'type' => 'vehicle',
            'name' => $vehicle->name,
            'detail_price' => "1 x {$vehicle->price}",
            'total' => $vehicle->price
        ];
        $total += $vehicle->price;
    }

    if ($accommodation) {
        $breakdown[] = [
            'type' => 'accommodation',
            'name' => $accommodation->name,
            'detail_price' => "1 x {$accommodation->price}",
            'total' => $accommodation->price
        ];
        $total += $accommodation->price;
    }

    $ticket = Ticket::create([
        'user_id' => $user->id,
        'destination_id' => $destination?->id,
        'vehicle_id' => $vehicle?->id,
        'accommodation_id' => $accommodation?->id,
        'ticket_code' => strtoupper(\Illuminate\Support\Str::random(10)),
        'expired_at' => $request->expired_at,
        'guest_count' => $guestCount,
        'total_price' => $total,
        'price_breakdown' => $breakdown
    ]);

    return response()->json([
        'ticket_code' => $ticket->ticket_code,
        'destination_name' => $destination?->name,
        'vehicle_name' => $vehicle?->name,
        'accommodation_name' => $accommodation?->name,
        // 'location' => $destination
        //     ? [
        //         'latitude' => $destination->latitude,
        //         'longitude' => $destination->longitude,
        //     ]
        //     : null,
        'expired_at' => $ticket->expired_at,
        'guest_count' => $guestCount,
        'total_price' => $total,
        'price_breakdown' => $breakdown
    ], 201);
}


    public function index()
    {
        return Ticket::with(['destination', 'vehicle', 'accommodation'])
            ->where('user_id', auth()->id())
            ->get()
            ->map(function ($ticket) {
                return [
                    'ticket_code' => $ticket->ticket_code,
                    'destination_name' => $ticket->destination?->name,
                    'vehicle_name' => $ticket->vehicle?->name,
                    'accommodation_name' => $ticket->accommodation?->name,
                    'location' => $ticket->destination?->location,
                    'expired_at' => $ticket->expired_at,
                    'guest_count' => $ticket->guest_count,
                    'total_price' => $ticket->total_price,
                    'price_breakdown' => $ticket->price_breakdown,
                    'created_at' => $ticket->created_at
                ];
            });
    }

    public function show($id)
    {
        $ticket = Ticket::with(['destination', 'vehicle', 'accommodation'])
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return [
            'ticket_code' => $ticket->ticket_code,
            'destination_name' => $ticket->destination?->name,
            'vehicle_name' => $ticket->vehicle?->name,
            'accommodation_name' => $ticket->accommodation?->name,
            'location' => $ticket->destination?->location,
            'expired_at' => $ticket->expired_at,
            'guest_count' => $ticket->guest_count,
            'total_price' => $ticket->total_price,
            'price_breakdown' => $ticket->price_breakdown,
            'created_at' => $ticket->created_at
        ];
    }

    public function destroy($id)
    {
        $ticket = Ticket::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted'
        ]);
    }
}
