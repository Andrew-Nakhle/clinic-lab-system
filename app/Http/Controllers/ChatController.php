<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // دالة لجلب الرسائل بينك وبين شخص معين
    public function index($receiverId)
    {
        return Message::where(function ($query) use ($receiverId) {
            $query->where('sender_id', auth()->id())->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($receiverId) {
            $query->where('sender_id', $receiverId)->where('receiver_id', auth()->id());
        })->orderBy('created_at', 'asc')->get();
    }

    // دالة لإرسال رسالة جديدة
    public function sendMessage(Request $request)
    {
        // 1. التحقق من البيانات
        $request->validate([
            'receiver_id' => 'required',
            'body' => 'required|string',
        ]);
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'body' => $request->body,
        ]);
        broadcast(new MessageSent($message));

        return response()->json(['status' => 'the message is sand', 'message' => $message]);
    }
    public function myChatUsers()
    {
        $doctorId = auth()->id();

        $userIds = Message::where(function ($query) use ($doctorId) {
            $query->where('sender_id', $doctorId)
                ->orWhere('receiver_id', $doctorId);
        })
            ->get(['sender_id', 'receiver_id'])
            ->flatMap(function ($message) use ($doctorId) {
                return [
                    $message->sender_id,
                    $message->receiver_id,
                ];
            })
            ->filter(fn ($id) => $id != $doctorId)
            ->unique()
            ->values();

        $users = User::whereIn('id', $userIds)
            ->get();

        return response()->json([
            'users' => $users,
        ]);
    }
}
