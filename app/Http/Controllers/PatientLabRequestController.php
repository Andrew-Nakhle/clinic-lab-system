<?php

namespace App\Http\Controllers;

use App\Models\MedicalTest;
use Illuminate\Http\Request;

class PatientLabRequestController extends Controller
{
    public function indexAvailableTests()
    {
        $tests = MedicalTest::all(); // أو حسب جدول التحاليل لديك
        return response()->json([
            'success' => true,
            'data' => $tests
        ]);
    }
}
