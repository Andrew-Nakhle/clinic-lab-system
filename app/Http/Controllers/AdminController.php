<?php

namespace App\Http\Controllers;

use App\Enums\UserStatus;
use App\Http\Resources\DoctorProfileResource;
use App\Http\Resources\LaboratoryProfileResource;
use App\Http\Resources\PatientResource;
use App\Http\Resources\Auth\RegisterResource;
use App\Models\Area;
use App\Models\DoctorProfile;
use App\Models\LaboratoryProfile;
use App\Models\PatientProfile;
use App\Models\SecretaryProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    // ---------------------------
    // Admin Profile
    // ---------------------------
    public function viewProfile()
    {
        $user = auth()->user();
        return response()->json([
            'status' => true,
            'data' => [
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $validatedData = $request->validate([
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|unique:users,phone,' . Auth::id(),
            'profile_image' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $path = $request->file('profile_image')->store('profile_images', 'public');
            $validatedData['profile_image'] = $path;
        }

        $user->update($validatedData);

        return response()->json([
            'status' => true,
            'data' => [
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            ]
        ]);
    }

    public function getAreas()
    {
        return response()->json(Area::all());
    }

    // ---------------------------
    // Doctors
    // ---------------------------
    public function viewDoctors()
    {
        $doctors = DoctorProfile::with('user')->get();
        return response()->json([
            'status' => true,
            'data' => DoctorProfileResource::collection($doctors),
        ]);
    }

    public function viewDoctor(int $id)
    {
        $doctor = DoctorProfile::with('user')->find($id);
        if (!$doctor) {
            return response()->json(['status' => false, 'message' => 'Doctor not found'], 404);
        }
        return response()->json(['status' => true, 'data' => new DoctorProfileResource($doctor)]);
    }

    public function updateDoctor(int $id)
    {
        $doctor = DoctorProfile::with('user')->find($id);
        if (!$doctor || !$doctor->user) {
            return response()->json(['status' => false, 'message' => 'Doctor or User not found'], 404);
        }

        $doctor->user->status = ($doctor->user->status === UserStatus::Active) ? UserStatus::Inactive : UserStatus::Active;
        $doctor->user->save();

        return response()->json([
            'status' => true,
            'message' => $doctor->user->status === UserStatus::Active ? 'User activated' : 'User deactivated',
            'new_status' => $doctor->user->status->value
        ]);
    }

    public function deleteDoctor(int $id)
    {
        $doctor = DoctorProfile::with('user')->find($id);
        if (!$doctor) {
            return response()->json(['status' => false, 'message' => 'Doctor not found'], 404);
        }
        $doctor->delete();
        return response()->json(['status' => true, 'message' => 'Doctor deleted successfully']);
    }

    // ---------------------------
    // Secretaries
    // ---------------------------
    public function viewSecretaries()
    {
        $secretaries = SecretaryProfile::with(['user', 'section'])->get();
        $data = $secretaries->map(function ($secretary) {
            return [
                'id' => $secretary->id,
                'name' => $secretary->user ? ($secretary->user->first_name . ' ' . $secretary->user->last_name) : 'غير معروف',
                'image' => $secretary->image ? asset('storage/' . $secretary->image) : null,
                'section' => $secretary->section ? $secretary->section->name : 'غير محدد',
                'status' => $secretary->user->status,
            ];
        });
        return response()->json(['status' => true, 'data' => $data]);
    }

    public function viewSecretary(int $id)
    {
        $secretary = SecretaryProfile::with(['user', 'section'])->find($id);
        if (!$secretary) {
            return response()->json(['status' => false, 'message' => 'Secretary not found'], 404);
        }
        $data = [
            'id' => $secretary->id,
            'name' => $secretary->user ? ($secretary->user->first_name . ' ' . $secretary->user->last_name) : 'غير معروف',
            'image' => $secretary->image ? asset('storage/' . $secretary->image) : null,
            'section' => $secretary->section->name ?? 'غير محدد',
            'status' => $secretary->user->status,
        ];
        return response()->json(['status' => true, 'data' => $data], 200);
    }

    public function updateSecretary(int $id)
    {
        $secretary = SecretaryProfile::with('user')->find($id);
        if (!$secretary || !$secretary->user) {
            return response()->json(['status' => false, 'message' => 'Secretary or User not found'], 404);
        }

        $secretary->user->status = ($secretary->user->status === UserStatus::Active) ? UserStatus::Inactive : UserStatus::Active;
        $secretary->user->save();

        return response()->json([
            'status' => true,
            'message' => $secretary->user->status === UserStatus::Active ? 'User activated' : 'User deactivated',
            'new_status' => $secretary->user->status->value
        ]);
    }

    public function deleteSecretary(int $id)
    {
        $secretary = SecretaryProfile::with('user')->find($id);
        if (!$secretary) {
            return response()->json(['status' => false, 'message' => 'Secretary not found'], 404);
        }
        $secretary->delete();
        return response()->json(['status' => true, 'message' => 'Secretary deleted successfully']);
    }

    // ---------------------------
    // Patients
    // ---------------------------
    public function viewPatients()
    {
        $patients = PatientProfile::with('user')->get();
        return response()->json(['status' => true, 'data' => PatientResource::collection($patients)]);
    }

    public function viewPatient(int $id)
    {
        $patient = PatientProfile::with('user')->find($id);
        if (!$patient) {
            return response()->json(['status' => false, 'message' => 'Patient not found'], 404);
        }
        return response()->json(['status' => true, 'data' => new PatientResource($patient)]);
    }

    public function updatePatient(int $id)
    {
        $patient = PatientProfile::with('user')->find($id);
        if (!$patient) {
            return response()->json(['status' => false, 'message' => 'Patient not found'], 404);
        }
        $patient->user->status = ($patient->user->status === UserStatus::Active) ? UserStatus::Inactive : UserStatus::Active;
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
            return response()->json(['status' => false, 'message' => 'Patient not found'], 404);
        }
        $patient->delete();
        return response()->json(['status' => true, 'message' => 'Patient deleted successfully']);
    }

    // ---------------------------
    // Laboratory
    // ---------------------------
    public function viewLaboratories()
    {
        $labs = LaboratoryProfile::with('user')->get();
        return response()->json(['status' => true, 'data' => LaboratoryProfileResource::collection($labs)]);
    }

    public function viewLaboratory(int $id)
    {
        $lab = LaboratoryProfile::with('user')->find($id);
        if (!$lab) {
            return response()->json(['status' => false, 'message' => 'Laboratory not found'], 404);
        }
        return response()->json(['status' => true, 'data' => new LaboratoryProfileResource($lab)]);
    }

    public function updateLaboratoryStatus(int $id)
    {
        $lab = LaboratoryProfile::with('user')->find($id);
        if (!$lab || !$lab->user) {
            return response()->json(['status' => false, 'message' => 'Laboratory or User not found'], 404);
        }
        $lab->user->status = ($lab->user->status === UserStatus::Active) ? UserStatus::Inactive : UserStatus::Active;
        $lab->user->save();
        return response()->json([
            'status' => true,
            'message' => 'Status updated successfully',
            'new_status' => $lab->user->status->value
        ]);
    }

    public function deleteLaboratory(int $id)
    {
        $lab = LaboratoryProfile::with('user')->find($id);
        if (!$lab) {
            return response()->json(['status' => false, 'message' => 'Laboratory not found'], 404);
        }
        $lab->delete();
        return response()->json(['status' => true, 'message' => 'Laboratory deleted successfully']);
    }
}
