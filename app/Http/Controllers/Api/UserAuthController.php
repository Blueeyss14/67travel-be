<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Cloudinary\Cloudinary;

class UserAuthController extends Controller
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

    public function register(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:users,email',
            'noTelpon' => 'required',
            'password' => 'required|min:8',
            'confirmPassword' => 'required|same:password',
            'profile_photo' => 'nullable|image|max:10240',
        ]);

        $profilePhotoUrl = null;
        if ($request->hasFile('profile_photo')) {
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('profile_photo')->getRealPath(),
                ['folder' => 'profile_photos']
            );
            $profilePhotoUrl = $uploaded['secure_url'];
        }

        $user = User::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'noTelpon' => $request->noTelpon,
            'password' => Hash::make($request->password),
            'role' => 'USER',
            'profile_photo' => $profilePhotoUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil',
            'data' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.']
            ]);
        }

        $token = $user->createToken('user-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $request->user()
        ]);
    }

    public function getAllUser()
    {
        return response()->json(User::all());
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'nama' => 'required',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'noTelpon' => 'required',
            'password' => 'nullable|min:8',
            'confirmPassword' => 'same:password',
            'profile_photo' => 'nullable|image|max:10240',
        ]);

        if ($request->hasFile('profile_photo')) {
            $uploaded = $this->cloudinary->uploadApi()->upload(
                $request->file('profile_photo')->getRealPath(),
                ['folder' => 'profile_photos']
            );
            $user->profile_photo = $uploaded['secure_url'];
        }

        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->noTelpon = $request->noTelpon;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User updated',
            'data' => $user
        ]);
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted'
        ]);
    }
}
