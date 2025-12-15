<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        return response()->json([
            'content' => Vehicle::where('user_id', auth()->id())->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'maxPassenger' => 'required|integer',
            'thumbnailUrl' => 'required|file',
        ]);

        if ($request->hasFile('thumbnailUrl')) {
            $validated['thumbnailUrl'] = $request->file('thumbnailUrl')->store('uploads', 'public');
        }

        $validated['user_id'] = auth()->id();

        return response()->json(Vehicle::create($validated), 201);
    }

    public function show($id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json($vehicle);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $data = $request->only(['name','price','maxPassenger']);

        if ($request->hasFile('thumbnailUrl')) {
            $data['thumbnailUrl'] = $request->file('thumbnailUrl')->store('uploads', 'public');
        }

        $vehicle->update($data);
        $vehicle->refresh();

        return response()->json([
            'message' => 'Vehicle updated',
            'data' => $vehicle
        ]);
    }

    public function destroy($id)
    {
        Vehicle::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['message' => 'deleted']);
    }
}
