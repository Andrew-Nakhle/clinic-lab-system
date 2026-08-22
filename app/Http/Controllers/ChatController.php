<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ChatController extends Controller
{
    // Fetch chat history between current user and receiver
    public function index($receiverId)
    {
        $authId = auth()->id();
        $doctor = DoctorProfile::where('user_id', $authId)->first();

        // 1. Logged-in user is a Doctor ($receiverId is Patient's user_id)
        if ($doctor) {
            $doctorId = $doctor->id;
            $patientUserId = $receiverId;

            return Message::where(function ($q) use ($authId, $patientUserId) {
                // Doctor sent to Patient
                $q->where('sender_id', $authId)->where('receiver_id', $patientUserId);
            })->orWhere(function ($q) use ($doctorId, $patientUserId) {
                // Patient sent to Doctor
                $q->where('sender_id', $patientUserId)->where('receiver_id', $doctorId);
            })->orderBy('created_at', 'asc')->get();
        }

        // 2. Logged-in user is a Patient ($receiverId is Doctor's doctor_id)
        $patientUserId = $authId;
        $doctorId = $receiverId;
        $doctorUserId = DoctorProfile::where('id', $doctorId)->value('user_id');

        $messages = Message::where(function ($q) use ($patientUserId, $doctorId) {
            $q->where('sender_id', $patientUserId)->where('receiver_id', $doctorId);
        })->orWhere(function ($q) use ($patientUserId, $doctorUserId) {
            $q->where('sender_id', $doctorUserId)->where('receiver_id', $patientUserId);
        })->orderBy('created_at', 'asc')->get();

        $messages->each(function ($message) {
            $message->body = Crypt::decryptString($message->body);
        });

        return $messages;
    }

    // Send a new message directly as received from Flutter
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required',
            'body'        => 'required|string',
        ]);

        $message = Message::create([
            'sender_id'   => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'body' => Crypt::encryptString($request->body),
        ]);

        broadcast(new MessageSent($message));

        return response()->json(['status' => 'the message is sand', 'message' => $message]);
    }

    // Fetch unique patients for the logged-in doctor
    public function myChatUsers()
    {
        $userId = auth()->id();
        $doctorId = DoctorProfile::where('user_id', $userId)->value('id');

        // Patients who sent messages to this specific doctor_id
        $patientsWhoMessagedMe = Message::where('receiver_id', $doctorId)->pluck('sender_id');

        // Patients who received messages from this doctor's user_id
        $patientsIMessaged = Message::where('sender_id', $userId)->pluck('receiver_id');

        // Combine Patient User IDs without cross-matching other doctors
        $patientUserIds = $patientsWhoMessagedMe->merge($patientsIMessaged)
            ->unique()
            ->reject(fn ($id) => $id == $userId || $id == $doctorId)
            ->values();

        $users = User::whereIn('id', $patientUserIds)->get();

        return response()->json([
            'users' => $users,
        ]);
    }
}
