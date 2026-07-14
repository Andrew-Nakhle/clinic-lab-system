<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\User;
use Illuminate\Http\Request;
use App\Enums\UserStatus;

class SuperAdminController extends Controller
{
    public function viewSections()
    {
        return response()->json(Section::all());
    }

    // تحديث سعر قسم معين
    public function updateSectionPrice(Request $request, $id)
    {
        $request->validate([
            'base_price' => 'required|numeric|min:0',
        ]);

        $section = Section::findOrFail($id);
        $section->update(['base_price' => $request->base_price]);

        return response()->json([
            'status' => true,
            'message' =>'the price has been updated',
            'data' => $section
        ]);
    }

    public function viewAdmins()
    {
        $all = User::role('admin')->get();
        return response()->json([
            'message' => 'All doctors retrieved successfully',
            'data' =>$all,
        ]);
    }
    public function viewAdmin($id)
    {
        $usre = user::find($id);
        return response()->json([
            'message' => 'Admin retrieved successfully',
            'data' => $usre,
        ]);
    }
    public function update(int $id)
    {
        $user = user::find($id);
        if($user->hasRole('admin')) {
            $user->status = $user->status === UserStatus::Active ? UserStatus::Inactive : UserStatus::Active;
            $user->save();
            return response()->json([
                'message' => 'user activated',
                'status' => $user->status->value
            ]);
        }else
        {
            return response()->json(['message' => "this user is not admin"], 403);
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
