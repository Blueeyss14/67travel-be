<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class AccommodationController extends Controller
{
    protected $cloudinary;

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

    public function index()
    {
        return response()->json([
            'content' => Accommodation::all()
        ]);
    }

    public function show($id)
    {
        return Accommodation::where('id', $id)->firstOrFail();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'price' => 'required|numeric',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        if ($request->hasFile('thumbnail')) {
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('thumbnail')->getRealPath(),
                ['folder' => 'accommodations']
            );
            $validated['thumbnail'] = $uploaded['secure_url'];
        }

        $validated['admin_id'] = $request->user()->id;

        $accommodation = Accommodation::create($validated);

        return response()->json($accommodation, 201);
    }

    public function update(Request $request, $id)
    {
        $accommodation = Accommodation::where('id', $id)
            ->where('admin_id', $request->user()->id)
            ->firstOrFail();

        $data = $request->validate([
            'name' => 'sometimes|string',
            'latitude' => 'sometimes|numeric',
            'longitude' => 'sometimes|numeric',
            'price' => 'sometimes|numeric',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ]);

        if ($request->hasFile('thumbnail')) {
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('thumbnail')->getRealPath(),
                ['folder' => 'accommodations']
            );
            $data['thumbnail'] = $uploaded['secure_url'];
        }

        $accommodation->update($data);

        return response()->json($accommodation);
    }

    public function destroy(Request $request, $id)
    {
        Accommodation::where('id', $id)
            ->where('admin_id', $request->user()->id)
            ->delete();

        return response()->json(['message' => 'deleted']);
    }
}
