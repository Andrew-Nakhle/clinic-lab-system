<?php

namespace App\Http\Controllers;

use App\Models\LabRequest;
use Illuminate\Http\Request;

class LabTechnicianController extends Controller
{
    // عرض طلبات التحليل بانتظار الاستلام
    public function index()
    {
        $requests = LabRequest::with(['doctor.user', 'patient.user', 'tests'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json(['data' => $requests]);
    }

    // المخبري يضغط "استلام الطلب" لبدء العمل
    public function accept(LabRequest $labRequest)
    {
        // تأكيد أن الطلب لم يأخذه فني آخر
        if ($labRequest->status !== 'pending') {
            return response()->json(['message' => 'هذا الطلب تم استلامه بالفعل من قبل فني آخر.'], 400);
        }

        $labRequest->update([
            'laboratory_profile_id' => auth()->user()->laboratoryProfile->id, // 💡 تأكد من وجود هذه العلاقة في User Model
            'status' => 'processing'
        ]);

        return response()->json(['message' => 'تم استلام الطلب وبدء التحليل الفعلي.']);
    }
    public function submitResults(Request $request, LabRequest $labRequest)
    {
        // حماية: التأكد أن المخبري الذي استلم الطلب هو نفسه من يدخل النتائج
  //      if ($labRequest->laboratory_profile_id !== auth()->user()->laboratoryProfile->id) {
    //        return response()->json(['message' => 'غير مصرح لك بإدخال نتائج هذا الطلب.'], 403);
      //  }

        $request->validate([
            'results' => 'required|array',
            'results.*.medical_test_id' => 'required|exists:medical_tests,id',
            'results.*.result_value' => 'required|string'
        ]);

        // تحديث جدول الربط
        foreach ($request->results as $result) {
            $labRequest->tests()->updateExistingPivot($result['medical_test_id'], [
                'result_value' => $result['result_value']
            ]);
        }
        $labRequest->update(['status' => 'completed']);
        $totalPrice = $labRequest->tests->sum('price');
        return response()->json(['totalPrice' => $totalPrice,
            'message'=>'the results have been submitted successfully.']);
    }
}
