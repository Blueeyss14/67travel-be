<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function notifications()
    {
        $user = auth()->user();
        $now = Carbon::now();

        $tickets = Ticket::where('user_id', $user->id)
            ->where('expired_at', '>', $now)
            ->get();

        $messages = [];

        foreach ($tickets as $ticket) {
            $expiredAt = Carbon::parse($ticket->expired_at);
            $diffInSeconds = $expiredAt->timestamp - $now->timestamp;

            $intervals = [
                120 => "Tiket {$ticket->ticket_code} hampir berakhir dalam 2 menit!",
                60  => "Tiket {$ticket->ticket_code} hampir berakhir dalam 1 menit!",
                30  => "Tiket {$ticket->ticket_code} hampir habis dalam 30 detik!"
            ];

            foreach ($intervals as $seconds => $text) {
                $exists = Notification::where('ticket_id', $ticket->id)
                    ->where('message', $text)
                    ->exists();

                if (!$exists && $diffInSeconds <= $seconds && $diffInSeconds > ($seconds - 30)) {
                    $notif = Notification::create([
                        'user_id' => $user->id,
                        'ticket_id' => $ticket->id,
                        'message' => $text,
                        'sent' => false
                    ]);

                    $messages[] = $text;
                }
            }
        }

        return response()->json([
            'messages' => $messages
        ]);
    }

    public function history()
    {
        $user = auth()->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }
}
