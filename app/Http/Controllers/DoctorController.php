<?php

namespace App\Http\Controllers;

use App\Enums\Appointment\AppointmentStatus;
use App\Http\Requests\Appointment\GetAppointmentsRequest;
use App\Http\Requests\Doctor\GetMedicalRecordRequest;
use App\Http\Requests\Doctor\UpdateProfileRequest;
use App\Http\Resources\Appointment\AppointmentResource;
use App\Models\Appointment;
use App\Models\PatientProfile;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $validated = $request->validated();

        if (!$user) {
            return response()->json(['message' => 'unauthorized.'], 401);
        }

        if ($request->hasFile('profile_image')) {
            $validated['profile_image'] = $request->file('profile_image')->store('profile_images', 'public');
            $user->doctor->update(['profile_image' => $validated['profile_image']]);
        }

        if ($request->hasFile('certification')) {
            $validated['certification'] = $request->file('certification')->store('certifications', 'public');
        }

        $user->update([
            'first_name' => $validated['first_name'] ?? $user->first_name,
            'last_name'  => $validated['last_name'] ?? $user->last_name,
            'phone'      => $validated['phone'] ?? $user->phone,
            'gender'     => $validated['gender'] ?? $user->gender,
            'birth_date' => $validated['birth_date'] ?? $user->birth_date,
        ]);

        $user->doctor->update([
            'experience_years' => $validated['experience_years'] ?? $user->doctor->experience_years,
            'certification'    => $validated['certification'] ?? $user->doctor->certification,
            'bio'              => $validated['bio'] ?? $user->doctor->bio,
            'section_id'       => $validated['section_id'] ?? $user->doctor->section_id,
            'specialization'   => $validated['specialization'] ?? $user->doctor->specialization
        ]);

        return response()->json([
            'message' => 'Profile updated successfully'
        ]);
    }

    public function todayAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();
        $query->with('patient.user')
            ->where('doctor_id', auth()->user()->doctor->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->whereDate('start_at', today())
            ->orderBy('start_at')
            ->get();

        return response()->json(['appointments' => AppointmentResource::collection($appointments)]);
    }

    public function upcomingAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();
        $query->with('patient.user')
            ->where('doctor_id', auth()->user()->doctor->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->where('status', AppointmentStatus::Booked->value)
            ->where('start_at', '>', now())
            ->orderBy('start_at')
            ->get();

        return response()->json(['appointments' => AppointmentResource::collection($appointments)]);
    }

    public function previousAppointments(GetAppointmentsRequest $request)
    {
        $query = Appointment::query();
        $query->with('patient.user')
            ->where('doctor_id', auth()->user()->doctor->id);

        if ($request->input('appointment_type')) {
            $query->where('appointment_type', $request->input('appointment_type'));
        }

        $appointments = $query
            ->where('start_at', '<', now())
            ->whereIn('status', [
                AppointmentStatus::Completed->value,
                AppointmentStatus::Cancelled->value,
            ])
            ->orderByDesc('start_at')
            ->get();

        return response()->json(['appointments' => AppointmentResource::collection($appointments)]);
    }

    public function getMedicalRecord(GetMedicalRecordRequest $request)
    {
        $validated = $request->validated();
        $patient = PatientProfile::with('user')->findOrFail($validated['patient_id']);

        if ($validated['medical_record_access_code'] != $patient->medical_record_access_code) {
            return response()->json(['message' => 'incorrect code'], 401);
        }

        $reports = $patient->reports()->with('doctor.user', 'appointment')->latest()->get();

        if ($reports->isEmpty()) {
            return response()->json([
                'message' => 'No reports found'
            ], 404);
        }

        $pdf = Pdf::loadView('pdf.medical_record', [
            'patient' => $patient,
            'reports' => $reports
        ]);

        return $pdf->download('medical_record.pdf');
    }
}
