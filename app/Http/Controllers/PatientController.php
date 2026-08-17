<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function patientAppointments()
    {
        $user = auth()->user();

        if (!$user || !$user->patient) {
            return response()->json([
                'message' => 'Patient profile not found.'
            ], 404);
        }

        $appointments = $user->patient->appointments()
            ->latest('start_at')
            ->get();

        return response()->json([
            'appointments' => $appointments
        ]);
    }
}
