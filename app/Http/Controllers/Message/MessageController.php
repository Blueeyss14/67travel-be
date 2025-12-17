<?php

namespace App\Http\Controllers\Message;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer',
            'userMessage' => 'nullable|string',
            'adminMessage' => 'nullable|string'
        ]);

        $user = User::findOrFail($request->userId);

        return Message::create([
            'userId' => $user->id,
            'userName' => $user->nama,
            'user_profile_photo' => $user->profile_photo,
            'userMessage' => $request->userMessage,
            'adminMessage' => $request->adminMessage,
            'timestamp' => now()
        ]);
    }

    public function getByUser($id)
    {
        $messages = Message::where('userId', $id)
            ->orderBy('id', 'desc')
            ->get();

        return $messages->map(function ($m) {
            if (!$m->user_profile_photo) {
                $user = User::find($m->userId);
                $m->user_profile_photo = $user?->profile_photo ?? null;
            }
            return $m;
        });
    }

    public function getAllUsersMessages()
{
    $userIds = Message::select('userId')->distinct()->pluck('userId');

    $messages = [];

    foreach ($userIds as $id) {
        $userMessages = Message::where('userId', $id)
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($m) {
                if (!$m->user_profile_photo) {
                    $user = User::find($m->userId);
                    $m->user_profile_photo = $user?->profile_photo ?? null;
                }
                return $m;
            });
        $messages = array_merge($messages, $userMessages->toArray());
    }

    return response()->json($messages);
}
}
