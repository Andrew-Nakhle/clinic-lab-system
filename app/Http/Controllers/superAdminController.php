<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\UserStatus;

class SuperAdminController extends Controller
{

    public function view_doctors()
    {
        $all = DoctorProfile::all();
        return response()->json([
            'message' => 'All doctors retrieved successfully',
            'data' => $all
        ]);
    }
    public function update(int $id)
    {
        $user = User::findorfail($id);
        $user->status = $user->status === UserStatus::Active ? UserStatus::Inactive : UserStatus::Active;
        return response()->json([
            'message' => 'Doctor activated',
            'status' => $user->status->value
        ]);
    }
}
