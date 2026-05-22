<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\UserStatus;

class SuperAdminController extends Controller
{

    public function viewAdmins()
    {
        $all = User::role('admin')->get();
        return response()->json([
            'message' => 'All doctors retrieved successfully',
            'data' =>$all,
        ]);
    }
    public function update(int $id)
    {
        $user = user::findorfail($id);
        if($user->hasRole('admin')) {
            $user->status = $user->status === UserStatus::Active ? UserStatus::Inactive : UserStatus::Active;
            $user->save();
            return response()->json([
                'message' => 'user activated',
                'status' => $user->status->value
            ]);
        }else
        {
            return response()->json(['message' => "You don't have permission to access this page"], 403);
        }
    }
    public function destroy(int $id)
    {
        $admin = User::findorfail($id);
        if($admin->hasRole('admin')) {
            $admin->delete();
            return response()->json(['message' => 'user deleted successfully']);
        }else{
            return response()->json(['message' => "You don't have permission to access this page"], 403);
        }
    }
}
