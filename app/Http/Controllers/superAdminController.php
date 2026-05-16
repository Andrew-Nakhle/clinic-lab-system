<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use Illuminate\Http\Request;

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
}
