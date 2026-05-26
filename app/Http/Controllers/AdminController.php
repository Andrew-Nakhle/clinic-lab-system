<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function viewDoctors(){
        $doctors = DoctorProfile::with('user');
     return response()->json(['doctors' => $doctors->get()]);
    }
    public function viewDoctor(int $id){
        $doctor = DoctorProfile::with('user')->where('id', $id)->first();
        return response()->json(['doctor' => $doctor]);
    }

        public function updateDoctor(int $id)
        {
            $doctor = DoctorProfile::with('user')->find($id);
            $doctor->user->status =
                $doctor->user->status === UserStatus::Active
                    ? UserStatus::Inactive
                    : UserStatus::Active;
            $doctor->user->save();
            return response()->json([
                'message' => 'user activated',
                'status' => $doctor->user->status->value
            ]);

        }
    public function delete(int $id)
    {
        $doctor = DoctorProfile::with('user')->where('id', $id)->first();
        $doctor->delete();
        return response()->json(['message' => 'user deleted successfully']);
    }

}
