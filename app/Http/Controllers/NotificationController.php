<?php

namespace App\Http\Controllers;

use App\Events\NotificationSent;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function test(Request $request)
    {
        broadcast(new NotificationSent(
            auth()->id(),
            'Test Notification',
            'Pusher notification is working!',
            'test',
            [
                'test' => true,
            ]
        ));

        return response()->json([
            'message' => 'Notification sent successfully.'
        ]);
    }
}
