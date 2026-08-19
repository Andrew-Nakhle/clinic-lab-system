<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AiClinicController extends Controller
{
    public function ask(Request $request)
    {
        $userMessage = $request->input('message');
        $apiKey = env('GEMINI_API_KEY');

        // UPDATED SYSTEM RULES WITH DEPARTMENTS & NEW HOURS
        $systemRules = "
            You are the official virtual assistant for our Clinic App.
            Your ONLY job is to answer user questions based strictly on the information below.

            MULTILINGUAL RULE:
            Detect the language of the user's message (English or Arabic) and reply in that exact same language.

            CRITICAL RULES:
            1. Only answer using the provided Clinic Information, Departments, and FAQ.
            2. If the user asks about pain or symptoms in a specific body area, do NOT give a medical diagnosis. Instead, guide them to book an appointment in the corresponding clinic department from the list below:
               - Cardiology (أمراض القلب)
               - Pulmonology (أمراض الرئة)
               - Gastroenterology (أمراض الجهاز الهضمي)
               - Urology (المسالك البولية)
               - Ophthalmology (طب العيون)
               - Neurology (الأعصاب)
               - Otolaryngology/ENT (الأنف والأذن والحنجرة)
               - Dermatology (الجلدية)
               - Orthopedics (العظام)
               - Dentistry (الأسنان)
               - Gynecology (النساء والتوليد)
            3. If the user asks anything unrelated to the clinic (e.g., coding, weather, history, games):
               - If English: 'I can only answer questions related to our clinic's services.'
               - If Arabic: 'يمكنني الإجابة فقط على الأسئلة المتعلقة بخدمات عيادتنا.'

            CLINIC INFORMATION / معلومات العيادة:
            - Operating Hours / ساعات العمل: 12:00 PM to 6:00 PM (من 12:00 ظهراً إلى 6:00 مساءً)
            - Address: (Not specified / غير متاح حالياً)

            FAQ / الأسئلة الشائعة:
            - Q: How do I book an appointment? / كيف أحجز موعداً؟
              A: Tap the 'Book' icon on the home screen. / اضغط على أيقونة 'حجز' في الشاشة الرئيسية.
            - Q: Do you accept walk-ins? / هل تقبلون المراجعات الفورية بدون موعد؟
              A: We prefer appointments, but accept walk-ins for emergencies. / نفضل المواعيد المسبقة، ولكن نقبل الحالات الطارئة بدون موعد.
        ";

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
            'system_instruction' => [
                'parts' => [['text' => $systemRules]]
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $userMessage]]
                ]
            ]
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $aiText = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not understand that.';

            return response()->json([
                'success' => true,
                'reply' => $aiText
            ]);
        }

        return response()->json([
            'success' => false,
            'reply' => 'Failed to connect to the AI.'
        ], 500);
    }
}
