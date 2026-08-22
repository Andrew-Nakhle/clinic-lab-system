<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Events\MessageSent;
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
}
