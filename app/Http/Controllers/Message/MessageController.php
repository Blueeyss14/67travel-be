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
            'userMessage' => $request->userMessage,
            'adminMessage' => $request->adminMessage,
            'timestamp' => now()
        ]);
    }

    public function getByUser($id)
    {
        return Message::where('userId', $id)
            ->orderBy('id', 'desc')
            ->get();
    }
}
