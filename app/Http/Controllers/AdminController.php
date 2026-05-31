<?php

namespace App\Http\Controllers;

use App\Http\Resources\Auth\RegisterResource;
use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function doctors(){
        $doctors=User::role('doctor')->with('doctor')->get();
        return response()->json(RegisterResource::collection($doctors));
    }
    public function secretaries(){
        $secretaries=User::role('secretary')->with('secretary')->get();
        return response()->json(RegisterResource::collection($secretaries));
    }
    public function patients(){
        $patients=User::role('patient')->with('patient')->get();
        return response()->json(RegisterResource::collection($patients));
    }
    public function doctor($id){
        $doctor=User::with('doctor')->find($id);
        return response()->json($doctor);
    }
    public function secretary($id){
        $secretary=User::with('secretary')->find($id);
        return response()->json($secretary);
    }

}
