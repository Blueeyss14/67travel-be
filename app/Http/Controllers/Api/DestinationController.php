<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index()
    {
        return response()->json([
            'content' => Destination::where('user_id', auth()->id())->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'owner' => 'required|string',
            'numberOfGuest' => 'required|integer',
            'maxOfGuest' => 'required|integer',
            'price' => 'required|numeric',
            'thumbnailUrl' => 'required|file',
            'facilities' => 'nullable|array',
            'description' => 'nullable|string',
        ]);

        if ($request->hasFile('thumbnailUrl')) {
            $validated['thumbnailUrl'] = $request->file('thumbnailUrl')->store('thumbnails', 'public');
        }

        $validated['imageUrls'] = [];
        if ($request->hasFile('imageUrls')) {
            foreach ($request->file('imageUrls') as $image) {
                $validated['imageUrls'][] = $image->store('images', 'public');
            }
        }

        $validated['user_id'] = auth()->id();

        return response()->json(Destination::create($validated), 201);
    }

    public function show($id)
    {
        return response()->json(Destination::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail());
    }

public function update(Request $request, $id)
{
    $destination = Destination::where('id', $id)
        ->where('user_id', auth()->id())
        ->firstOrFail();

    $data = $request->only([
        'name',
        'location',
        'owner',
        'numberOfGuest',
        'maxOfGuest',
        'price',
        'description'
    ]);

    if ($request->has('facilities')) {
        $data['facilities'] = is_array($request->facilities)
            ? $request->facilities
            : json_decode($request->facilities, true);
    }

    if ($request->hasFile('thumbnailUrl')) {
        $data['thumbnailUrl'] = $request->file('thumbnailUrl')->store('thumbnails', 'public');
    }

    if ($request->hasFile('imageUrls')) {
        $images = [];
        foreach ($request->file('imageUrls') as $image) {
            $images[] = $image->store('images', 'public');
        }
        $data['imageUrls'] = $images;
    }

    $destination->update($data);
    $destination->refresh();

    return response()->json([
        'message' => 'Destination updated successfully',
        'data' => $destination
    ]);
}


    public function destroy($id)
    {
        Destination::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json([
            'message' => 'deleted'
        ]);
    }
}
