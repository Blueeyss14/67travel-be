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
            'expired_at' => 'required|date'
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
        $guestCount = $destination?->numberOfGuest ?? 1;

        if ($destination) {
            $subtotal = $destination->numberOfGuest * $destination->price;
            $breakdown[] = [
                'type' => 'destination',
                'name' => $destination->name,
                'detail_price' => "{$destination->numberOfGuest} x {$destination->price}",
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
            'ticket_code' => strtoupper(Str::random(10)),
            'expired_at' => $request->expired_at,
            'guest_count' => $guestCount,
            'total_price' => $total,
            'price_breakdown' => $breakdown
        ]);

        return response()->json([
            'ticket_code' => $ticket->ticket_code,
            'nama' => $user->nama,
            'kendaraan' => $vehicle?->name,
            'pengunjung' => $guestCount,
            'waktu' => $ticket->expired_at,
            'akomodasi' => $accommodation?->name,
            'price' => $total,
            'rincian' => $breakdown
        ], 201);
    }

    public function index()
    {
        return Ticket::where('user_id', auth()->id())->get();
    }

    public function show($id)
    {
        return Ticket::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();
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
