<?php

namespace App\Http\Controllers;

use App\Http\Resources\LabRequestResource;
use App\Models\LabRequest;
use Illuminate\Http\Request;

class LabTechnicianController extends Controller
{

    public function viewProfile()
    {
        $user = auth()->user()->load('laboratory');
        $lab = $user->laboratory;

        return response()->json([
            'status' => true,
            'data' => [
                'full_name' => $user->first_name . ' ' . $user->last_name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'lab_info'  => $lab ? [
                    'image'     => $lab->image ? asset('storage/' . $lab->image) : null,
                    'license_number' => $lab->license_number,
                    'section_id'     => $lab->section_id,
                ] : null,
            ]
        ]);
    }
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $lab = $user->laboratory; // تأكد من اسم العلاقة في موديل المستخدم (هل هو laboratory أم laboratoryProfile؟)

        if (!$lab) {
            return response()->json(['message' => 'ملف المختبر غير موجود'], 404);
        }

        // 1. التحقق من صحة البيانات (مع إصلاح الـ Unique للـ Phone)
        $request->validate([
            'first_name'     => 'sometimes|string|max:255',
            'last_name'      => 'sometimes|string|max:255',
            'phone'          => 'sometimes|string|max:15|unique:users,phone,' . $user->id,
            'license_number' => 'sometimes|string|max:100',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. تحديث بيانات المستخدم
        $user->update($request->only(['first_name', 'last_name', 'phone']));

        // 3. تحديث بيانات المختبر
        $labData = $request->only(['license_number', 'section_id']);

        // معالجة الصورة بشكل منفصل
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('laboratory_images', 'public');
            $labData['image'] = $path;
        }

        // التحديث هنا يتم باستخدام المصفوفة التي تحتوي على البيانات الجديدة + مسار الصورة إن وجد
        $lab->update($labData);

        return response()->json([
            'status' => true,
            'message' => 'تم التحديث بنجاح',
            'data' => [
                'owner_name'     => $user->first_name . ' ' . $user->last_name,
                'license_number' => $lab->fresh()->license_number,
                'image'          => $lab->fresh()->image ? asset('storage/' . $lab->fresh()->image) : null,
            ]
        ]);
    }

    // عرض طلبات التحليل بانتظار الاستلام
    public function index()
    {
        $requests = LabRequest::with(['doctor.user', 'patient.user', 'tests'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return response()->json(['data' =>  LabRequestResource::collection( $requests)]);
    }

    // المخبري يضغط "استلام الطلب" لبدء العمل
    /*public function accept(LabRequest $labRequest)
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
        return response()->json([
            'success' => true,
            'totalPrice' => $totalPrice.'$',
            'message'=>'the results have been submitted successfully.']);
    }*/
    public function submitResults(Request $request, LabRequest $labRequest)
    {
        // 1. التحقق من صحة البيانات
        $request->validate([
            'results' => 'required|array',
            'results.*.medical_test_id' => 'required|exists:medical_tests,id',
            'results.*.result_value' => 'required|string'
        ]);

        // 2. الدمج الذكي: إذا كان الطلب لا يزال "معلقاً"، نقوم باستلامه تلقائياً
        if ($labRequest->status === 'pending') {
            $labRequest->update([
                'laboratory_profile_id' => auth()->user()->laboratory->id,
                'status' => 'processing'
            ]);
        }

        // 3. تحديث النتائج في جدول الربط
        foreach ($request->results as $result) {
            $labRequest->tests()->updateExistingPivot($result['medical_test_id'], [
                'result_value' => $result['result_value']
            ]);
        }

        // 4. إنهاء الطلب وحساب السعر
        $labRequest->update(['status' => 'completed']);
        $totalPrice = $labRequest->tests->sum('price');

        return response()->json([
            'success'    => true,
            'totalPrice' => $totalPrice . '$',
            'message'    => 'test submitted successfully'
        ]);
    }
}
