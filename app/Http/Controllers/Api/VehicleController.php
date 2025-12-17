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
            'content' => Vehicle::all()
        ]);
    }

    public function show($id)
    {
        return Vehicle::where('id', $id)->firstOrFail();
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

        $validated['admin_id'] = $request->user()->id;

        $vehicle = Vehicle::create($validated);

        return response()->json($vehicle, 201);
    }

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::where('id', $id)
            ->where('admin_id', $request->user()->id)
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

    public function destroy(Request $request, $id)
    {
        Vehicle::where('id', $id)
            ->where('admin_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'deleted']);
    }
}
