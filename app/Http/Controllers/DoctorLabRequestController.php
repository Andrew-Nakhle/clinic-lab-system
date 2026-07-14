<?php

namespace App\Http\Controllers;

use App\Http\Resources\LabRequestResource;
use App\Models\LabRequest;
use Illuminate\Http\Request;

class DoctorLabRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_profile_id' => 'required|exists:patient_profiles,id',
            'doctor_notes'       => 'nullable|string',
            'selected_tests'     => 'required|array',
            'selected_tests.*'   => 'exists:medical_tests,id'
        ]);

        $doctor = auth()->user()->doctor;

        if (!$doctor) {
            return response()->json(['message' => 'you are not a doctor'], 403);
        }

        $labRequest = LabRequest::create([
            'doctor_profile_id'  => $doctor->id,
            'patient_profile_id' => $validated['patient_profile_id'],
            'doctor_notes'       => $validated['doctor_notes'],
            'status'             => 'pending'
        ]);

        $labRequest->tests()->attach($validated['selected_tests']);
        $labRequest->load(['tests', 'doctor.user', 'patient.user']);
        return response()->json([
            'message' => 'sending lab request success',
            'data'    => new LabRequestResource($labRequest),
        ], 201);
    }

    public function show(LabRequest $labRequest)
    {

        // شحن البيانات متضمنة الـ pivot (result_value) لعرض النتيجة والمدى الطبيعي
        return response()->json([
            'success' => true,
            'data' => new LabRequestResource($labRequest->load(['doctor.user', 'patient.user', 'laboratory.user', 'tests'])),

        ]);
    }
}
