<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use App\Models\SecretaryProfile;

class AdminController extends Controller
{
    // ---------------------------
    // Doctors
    // ---------------------------

    public function viewDoctors()
    {
        $doctors = DoctorProfile::with('user')->get();

        return response()->json([
            'status' => true,
            'data' => $doctors
        ]);
    }

    public function viewDoctor(int $id)
    {
        $doctor = DoctorProfile::with('user')->find($id);

        if (!$doctor) {
            return response()->json([
                'status' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $doctor
        ]);
    }

    public function updateDoctor(int $id)
    {
        $doctor = DoctorProfile::with('user')->find($id);

        if (!$doctor) {
            return response()->json([
                'status' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        if (!$doctor->user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found for this doctor'
            ], 404);
        }

        // Toggle status
        $doctor->user->status =
            $doctor->user->status === UserStatus::Active
                ? UserStatus::Inactive
                : UserStatus::Active;

        $doctor->user->save();

        return response()->json([
            'status' => true,
            'message' => $doctor->user->status === UserStatus::Active
                ? 'User activated'
                : 'User deactivated',
            'new_status' => $doctor->user->status->value
        ]);
    }

    public function deleteDoctor(int $id)
    {
        $doctor = DoctorProfile::with('user')->find($id);

        if (!$doctor) {
            return response()->json([
                'status' => false,
                'message' => 'Doctor not found'
            ], 404);
        }

        $doctor->delete();

        return response()->json([
            'status' => true,
            'message' => 'Doctor deleted successfully'
        ]);
    }


    // ---------------------------
    // Secretaries
    // ---------------------------

    public function viewSecretaries()
    {
        $secretaries = SecretaryProfile::with('user')->get();

        return response()->json([
            'status' => true,
            'data' => $secretaries
        ]);
    }

    public function viewSecretary(int $id)
    {
        $secretary = SecretaryProfile::with('user')->find($id);
        if (!$secretary) {
            return response()->json([
                'status' => false,
                'message' => 'Secretary not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $secretary
        ]);
    }

    public function updateSecretary(int $id)
    {
        $secretary = SecretaryProfile::with('user')->find($id);

        if (!$secretary) {
            return response()->json([
                'status' => false,
                'message' => 'Secretary not found'
            ], 404);
        }

        if (!$secretary->user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found for this secretary'
            ], 404);
        }

        $secretary->user->status =
            $secretary->user->status === UserStatus::Active
                ? UserStatus::Inactive
                : UserStatus::Active;

        $secretary->user->save();

        return response()->json([
            'status' => true,
            'message' => $secretary->user->status === UserStatus::Active
                ? 'User activated'
                : 'User deactivated',
            'new_status' => $secretary->user->status->value
        ]);
    }

    public function deleteSecretary(int $id)
    {
        $secretary = SecretaryProfile::with('user')->find($id);

        if (!$secretary) {
            return response()->json([
                'status' => false,
                'message' => 'Secretary not found'
            ], 404);
        }

        $secretary->delete();

        return response()->json([
            'status' => true,
            'message' => 'Secretary deleted successfully'
        ]);
    }
    //----------------
    // Patients
    //----------------

    public function viewPatients()
    {
        $patients = PatientProfile::with('user')->get();

        return response()->json([
            'status' => true,
            'data' => $patients
        ]);
    }

    public function viewPatient(int $id)
    {
        $patient = PatientProfile::with('user')->find($id);

        if (!$patient) {
            return response()->json([
                'status' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $patient
        ]);
    }

    public function updatePatient(int $id)
    {
        $patient = PatientProfile::with('user')->find($id);

        if (!$patient) {
            return response()->json([
                'status' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        // Toggle status
        $patient->user->status =
            $patient->user->status === UserStatus::Active
                ? UserStatus::Inactive
                : UserStatus::Active;

        $patient->user->save();

        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'new_status' => $patient->user->status
        ]);
    }

    public function deletePatient(int $id)
    {
        $patient = PatientProfile::with('user')->find($id);

        if (!$patient) {
            return response()->json([
                'status' => false,
                'message' => 'Patient not found'
            ], 404);
        }

        $patient->delete();

        return response()->json([
            'status' => true,
            'message' => 'Patient deleted successfully'
        ]);
    }



}
