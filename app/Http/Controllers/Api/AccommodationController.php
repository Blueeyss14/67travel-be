<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    public function index()
    {
        return response()->json([
            'content' => Accommodation::where('user_id', auth()->id())->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'price' => 'required|numeric',
        ]);

        $validated['user_id'] = auth()->id();
        return response()->json(Accommodation::create($validated), 201);
    }

    public function show($id)
    {
        $data = Accommodation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json($data);
    }

    public function update(Request $request, $id)
    {
        $accommodation = Accommodation::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $accommodation->update($request->only(['name','latitude','longitude','price']));
        $accommodation->refresh();

        return response()->json([
            'message' => 'Accommodation updated',
            'data' => $accommodation
        ]);
    }

    public function destroy($id)
    {
        Accommodation::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['message' => 'deleted']);
    }
}
