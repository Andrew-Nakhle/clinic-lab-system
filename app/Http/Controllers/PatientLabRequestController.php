<?php

namespace App\Http\Controllers;

use App\Models\LabRequest;
use App\Models\MedicalTest;
use Illuminate\Http\Request;

class PatientLabRequestController extends Controller
{

public function receivedRequests()
{
    $patient = auth()->user()->patient;

    if (!$patient) {
     return response()->json(['message' => 'ملف المريض غير موجود'], 404);
       }

    $requests = LabRequest::with(['doctor.user', 'patient.user', 'laboratory.user', 'tests'])
    ->where('patient_profile_id', $patient->id)
    ->where('status', 'completed')
    ->latest()
    ->get();

     return response()->json([
    'success' => true,
    'data'    => $requests
   ]);
  }
    public function indexAvailableTests()
    {
        $tests = MedicalTest::all(); // أو حسب جدول التحاليل لديك
        return response()->json([
            'success' => true,
            'data' => $tests
        ]);
    }



    public function store(Request $request)
    {
        $validated = $request->validate([
            'selected_tests'         => 'required|array',
            'selected_tests.*'       => 'exists:medical_tests,id',
            'collection_date'        => 'required|date',
            'collection_time'        => 'required|string',
            'collection_type'        => 'required|in:laboratory_visit,home_collection', // زيارة مختبر أو عينة منزلية
            'reserve_parking'        => 'nullable|boolean', // حجز موقف سيارة (+3$)
            'doctor_notes'           => 'nullable|string',
        ]);

        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json(['message' => 'ملف المريض غير موجود'], 404);
        }

        // إنشاء طلب التحليل الجديد
        $labRequest = LabRequest::create([
            'patient_profile_id'    => $patient->id,
            'doctor_profile_id'     => null, // لأن الطلب من قبل المريض مباشرة وليس طبيب
            'doctor_notes'          => $validated['doctor_notes'] ?? null,
            'collection_date'       => $validated['collection_date'],
            'collection_time'       => $validated['collection_time'],
            'collection_type'       => $validated['collection_type'],
            'reserve_parking'       => $validated['reserve_parking'] ?? false,
            'status'                => 'pending'
        ]);

        // ربط التحاليل المختارة بطلب التحليل
        $labRequest->tests()->attach($validated['selected_tests']);

        // حساب السعر الإجمالي (أسعار التحاليل + تكلفة المنزل إن وجدت + تكلفة الموقف إن وجدت)
        $testsPrice = $labRequest->tests->sum('price');
        $homeCollectionFee = ($validated['collection_type'] === 'home_collection') ? 5.00 : 0.00;
        $parkingFee = (!empty($validated['reserve_parking'])) ? 3.00 : 0.00;
        $totalPrice = $testsPrice + $homeCollectionFee + $parkingFee;

        $labRequest->load(['tests', 'patient.user']);

        return response()->json([
            'success'    => true,
            'message'    => 'تم إرسال طلب التحليل بنجاح',
            'totalPrice' => $totalPrice . '$',
            'data'       => $labRequest,
        ], 201);
    }

    public function pendingRequests()
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            return response()->json(['message' => 'ملف المريض غير موجود'], 404);
        }

        $requests = LabRequest::with(['doctor.user', 'patient.user', 'laboratory.user', 'tests'])
            ->where('patient_profile_id', $patient->id)
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $requests
        ]);
    }



    public function destroy(LabRequest $labRequest)
    {
        $patient = auth()->user()->patient;

        // التأكد أن الطلب يخص المريض نفسه حمايةً للبيانات
        if (!$patient || $labRequest->patient_profile_id !== $patient->id) {
            return response()->json(['message' => 'غير مصرح لك بحذف هذا الطلب'], 403);
        }

        // حذف ارتباط التحاليل أولاً ثم حذف الطلب
        $labRequest->tests()->detach();
        $labRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف طلب التحليل بنجاح'
        ]);
    }

}
