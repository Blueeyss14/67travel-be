<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;

class DestinationController extends Controller
{
    public function index()
    {
        $destinations = Destination::all()->map(function ($destination) {
            $destinationArray = $destination->toArray();

            $destinationArray['ratings'] = collect($destination->ratings ?? [])->map(function ($r) {
                $user = \App\Models\User::find($r['user_id']);
                $r['user_profile_photo'] = $user?->profile_photo ?? null;
                return $r;
            })->toArray();

            return $destinationArray;
        });

        return response()->json(['content' => $destinations]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'location' => 'required|string',
            'owner' => 'required|string',
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

        $validated['admin_id'] = $request->user()->id;

        $destination = Destination::create($validated);

        return response()->json($destination, 201);
    }

    public function show($id)
    {
        $destination = Destination::findOrFail($id);

        $destinationArray = $destination->toArray();
        $destinationArray['ratings'] = collect($destination->ratings ?? [])->map(function ($r) {
            $user = \App\Models\User::find($r['user_id']);
            $r['user_profile_photo'] = $user?->profile_photo ?? null;
            return $r;
        })->toArray();

        return response()->json($destinationArray);
    }

    public function update(Request $request, $id)
    {
        $destination = Destination::where('id', $id)
            ->where('admin_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->only([
            'name', 'location', 'owner', 'maxOfGuest', 'price', 'description'
        ]);

        if ($request->has('facilities')) {
            $data['facilities'] = $request->facilities;
        }

        if ($request->hasFile('thumbnailUrl')) {
            $data['thumbnailUrl'] = $request->file('thumbnailUrl')->store('thumbnails', 'public');
        }

        if ($request->hasFile('imageUrls')) {
            $imgs = [];
            foreach ($request->file('imageUrls') as $image) {
                $imgs[] = $image->store('images', 'public');
            }
            $data['imageUrls'] = $imgs;
        }

        $destination->update($data);

        return response()->json($destination);
    }

    public function destroy(Request $request, $id)
    {
        Destination::where('id', $id)
            ->where('admin_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'deleted']);
    }

    public function rate(Request $request, $id)
    {
        $request->validate([
            'rate' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string'
        ]);

        $destination = Destination::findOrFail($id);
        $user = $request->user();

        $ratings = $destination->ratings ?? [];

        foreach ($ratings as $r) {
            if ($r['user_id'] === $user->id) {
                return response()->json(['message' => 'User already rated'], 422);
            }
        }

        $ratings[] = [
            'user_id' => $user->id,
            'user_name' => $user->nama,
            'user_profile_photo' => $user->profile_photo,
            'rate' => $request->rate,
            'description' => $request->description,
            'created_at' => now()->toDateTimeString()
        ];

        $avg = collect($ratings)->avg('rate');

        $destination->update([
            'ratings' => $ratings,
            'rating' => round($avg, 1)
        ]);

        return response()->json([
            'message' => 'Rating added',
            'rating' => $destination->rating,
            'ratings' => $ratings
        ]);
    }
}
