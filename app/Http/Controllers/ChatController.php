<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // دالة لجلب الرسائل بينك وبين شخص معين
    public function index($receiverId)
    {
        $userId = auth()->id();
        $doctor = DoctorProfile::where('user_id', $userId)->first();
        $doctorId = $doctor ? $doctor->id : null;

        return Message::where(function ($query) use ($userId, $receiverId) {
            $query->where('sender_id', $userId)->where('receiver_id', $receiverId);
        })->orWhere(function ($query) use ($userId, $doctorId, $receiverId) {
            $query->where('sender_id', $receiverId)
                ->where(function ($q) use ($userId, $doctorId) {
                    $q->where('receiver_id', $userId);
                    if ($doctorId) {
                        $q->orWhere('receiver_id', $doctorId);
                    }
                });
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

        $receiverId = $request->receiver_id;
        $doctor = DoctorProfile::find($receiverId);
        if ($doctor && $doctor->user_id) {
            $receiverId = $doctor->user_id;
        }

        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $receiverId,
            'body' => $request->body,
        ]);
        broadcast(new MessageSent($message));

        return response()->json(['status' => 'the message is sand', 'message' => $message]);
    }

    public function myChatUsers()
    {
        $userId = auth()->id();
        $doctor = DoctorProfile::where('user_id', $userId)->first();
        $doctorId = $doctor ? $doctor->id : null;

        // 1. Get IDs of users who received messages from this doctor (or doctor ID)
        $sentTo = Message::where('sender_id', $userId)
            ->when($doctorId, fn ($q) => $q->orWhere('sender_id', $doctorId))
            ->pluck('receiver_id');

        // 2. Get IDs of users who sent messages to this doctor (or doctor ID)
        $receivedFrom = Message::where('receiver_id', $userId)
            ->when($doctorId, fn ($q) => $q->orWhere('receiver_id', $doctorId))
            ->pluck('sender_id');

        // 3. Merge, remove duplicates, and exclude current user ID / doctor ID
        $userIds = $sentTo->merge($receivedFrom)
            ->unique()
            ->reject(fn ($id) => $id == $userId || ($doctorId && $id == $doctorId))
            ->values();

        // 4. Fetch user details
        $users = User::whereIn('id', $userIds)->get();

        return response()->json([
            'users' => $users,
        ]);
    }

}
