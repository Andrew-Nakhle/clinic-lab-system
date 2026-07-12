<?php

namespace App\Http\Controllers;

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

        // ✅ تصحيح اسم العلاقة إلى doctor وتأمينها باستخدام الـ Nullsafe Operator
        $doctor = auth()->user()->doctor;

        if (!$doctor) {
            return response()->json([
                'message' => 'you are not a doctor'
            ], 403);
        }

        $labRequest = LabRequest::create([
            'doctor_profile_id'  => $doctor->id, // تم التعديل هنا
            'patient_profile_id' => $validated['patient_profile_id'],
            'doctor_notes'       => $validated['doctor_notes'],
            'status'             => 'pending'
        ]);

        // ربط التحاليل المتعددة دفعة واحدة بجدول lab_request_items
        $labRequest->tests()->attach($validated['selected_tests']);

        return response()->json([
            'message' => 'sending lab request success',
            'data'    => $labRequest->load('tests')
        ], 201);
    }

    public function show(LabRequest $labRequest)
    {
        $totalPrice = $labRequest->tests->sum('price');
        // شحن البيانات متضمنة الـ pivot (result_value) لعرض النتيجة والمدى الطبيعي
        return response()->json([
            'success' => true,
            'data' => $labRequest->load(['doctor.user', 'patient.user', 'laboratory.user', 'tests']),
            'totalPrice' => $totalPrice

        ]);
    }
}
