<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class DestinationController extends Controller
{
    protected Cloudinary $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
                'api_key'    => env('CLOUDINARY_API_KEY'),
                'api_secret' => env('CLOUDINARY_API_SECRET'),
            ],
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $destinations = Destination::all()->map(function ($destination) use ($user) {
            $destinationArray = $destination->toArray();
            $destinationArray['ratings'] = collect($destination->ratings ?? [])->map(function ($r) {
                $u = \App\Models\User::find($r['user_id']);
                $r['user_profile_photo'] = $u?->profile_photo ?? null;
                return $r;
            })->toArray();
            $destinationArray['bookmark'] = in_array($user->id, $destination->user_bookmarks ?? []);
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
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('thumbnailUrl')->getRealPath(),
                ['folder' => 'destinations/thumbnails']
            );
            $validated['thumbnailUrl'] = $uploaded['secure_url'];
        }

        $validated['imageUrls'] = [];
        if ($request->hasFile('imageUrls')) {
            foreach ($request->file('imageUrls') as $image) {
                $uploaded = $this->cloudinary->uploadApi()->upload(
                    $image->getRealPath(),
                    ['folder' => 'destinations/images']
                );
                $validated['imageUrls'][] = $uploaded['secure_url'];
            }
        }

        $validated['admin_id'] = $request->user()->id;
        $validated['user_bookmarks'] = [];

        $destination = Destination::create($validated);

        return response()->json($destination, 201);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $destination = Destination::findOrFail($id);

        $destinationArray = $destination->toArray();
        $destinationArray['ratings'] = collect($destination->ratings ?? [])->map(function ($r) {
            $u = \App\Models\User::find($r['user_id']);
            $r['user_profile_photo'] = $u?->profile_photo ?? null;
            return $r;
        })->toArray();
        $destinationArray['bookmark'] = in_array($user->id, $destination->user_bookmarks ?? []);

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
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('thumbnailUrl')->getRealPath(),
                ['folder' => 'destinations/thumbnails']
            );
            $data['thumbnailUrl'] = $uploaded['secure_url'];
        }

        if ($request->hasFile('imageUrls')) {
            $imgs = [];
            foreach ($request->file('imageUrls') as $image) {
                $uploaded = $this->cloudinary->uploadApi()->upload(
                    $image->getRealPath(),
                    ['folder' => 'destinations/images']
                );
                $imgs[] = $uploaded['secure_url'];
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
            if ((int)$r['user_id'] === (int)$user->id) {
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

    public function toggleBookmark(Request $request, $id)
    {
        $user = $request->user();
        $destination = Destination::findOrFail($id);
        $bookmarks = $destination->user_bookmarks ?? [];

        if (in_array($user->id, $bookmarks)) {
            $bookmarks = array_filter($bookmarks, fn($uid) => $uid !== $user->id);
            $destination->update(['user_bookmarks' => array_values($bookmarks)]);
            return response()->json(['bookmark' => false]);
        } else {
            $bookmarks[] = $user->id;
            $destination->update(['user_bookmarks' => $bookmarks]);
            return response()->json(['bookmark' => true]);
        }
    }
}
