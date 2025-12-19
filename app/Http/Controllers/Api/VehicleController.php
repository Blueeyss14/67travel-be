<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class VehicleController extends Controller
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
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('thumbnailUrl')->getRealPath(),
                ['folder' => 'vehicles']
            );
            $validated['thumbnailUrl'] = $uploaded['secure_url'];
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
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('thumbnailUrl')->getRealPath(),
                ['folder' => 'vehicles']
            );
            $data['thumbnailUrl'] = $uploaded['secure_url'];
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
