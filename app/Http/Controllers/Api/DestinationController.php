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
        'content' => Destination::all()
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
            $validated['thumbnailUrl'] =
                $request->file('thumbnailUrl')->store('thumbnails', 'public');
        }

        $validated['imageUrls'] = [];
        if ($request->hasFile('imageUrls')) {
            foreach ($request->file('imageUrls') as $image) {
                $validated['imageUrls'][] =
                    $image->store('images', 'public');
            }
        }

        $validated['user_id'] = auth()->id();

        return response()->json(
            Destination::create($validated),
            201
        );
    }

    public function show($id)
    {
        return response()->json(
            Destination::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail()
        );
    }

    //rating
    public function rate(Request $request, $id)
    {
        $request->validate([
            'rate' => 'required|integer|min:1|max:5',
            'description' => 'nullable|string'
        ]);

        $destination = Destination::findOrFail($id);
        $user = auth()->user();

        $ratings = $destination->ratings ?? [];

        foreach ($ratings as $r) {
            if ($r['user_id'] === $user->id) {
                return response()->json([
                    'message' => 'User already rated'
                ], 422);
            }
        }

        $ratings[] = [
            'user_id' => $user->id,
            'user_name' => $user->nama,
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
            $data['facilities'] = $request->facilities;
        }

        if ($request->hasFile('thumbnailUrl')) {
            $data['thumbnailUrl'] =
                $request->file('thumbnailUrl')->store('thumbnails', 'public');
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

    public function destroy($id)
    {
        Destination::where('id', $id)
            ->where('user_id', auth()->id())
            ->delete();

        return response()->json(['message' => 'deleted']);
    }
}
