<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Controller Imports
use App\Http\Controllers\{
    AuthController,
    OtpController,
    SuperAdminController,
    AdminController,
    DoctorLabRequestController,
    LabTechnicianController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'loginUser']);
    Route::post('login/manager', [AuthController::class, 'loginManager']);
    Route::post('register/patient', [AuthController::class, 'registerPatient']);
});

Route::post('verifyOtp', [OtpController::class, 'verifyLoginOtp']);
Route::post('resendOtp', [OtpController::class, 'resendLoginOtp']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // User General
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Super Admin Routes
    Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('super-admin')->group(function () {

        // إدارة الإداريين (Admins)
        Route::prefix('admins')->group(function () {
            Route::get('/', [SuperAdminController::class, 'viewAdmins']);
            Route::post('/register', [AuthController::class, 'registerAdmin']);
            Route::get('/{id}', [SuperAdminController::class, 'viewAdmin']);
            Route::patch('/{id}/status', [SuperAdminController::class, 'update']);
            Route::delete('/{id}', [SuperAdminController::class, 'destroy']);
        });

        // إدارة الأقسام والأسعار
        Route::prefix('sections')->group(function () {
            Route::get('/', [SuperAdminController::class, 'viewSections']);
            Route::patch('/{id}/price', [SuperAdminController::class, 'updateSectionPrice']);
        });
    });
    // Admin Routes (Role: admin + Middleware: active)
    Route::middleware(['role:admin', 'active'])->group(function () {
            Route::get('/viewProfile', [AdminController::class, 'viewProfile']);
            Route::patch('/updateProfile', [AdminController::class, 'updateProfile']);





        // Doctors
        Route::prefix('doctors')->group(function () {
            Route::post('/register', [AuthController::class, 'registerDoctor']);
            Route::get('/', [AdminController::class, 'viewDoctors']);
            Route::get('/{id}', [AdminController::class, 'viewDoctor']);
            Route::patch('/{id}/status', [AdminController::class, 'updateDoctor']);
            Route::delete('/{id}', [AdminController::class, 'deleteDoctor']);
        });

        // Secretaries
        Route::prefix('secretaries')->group(function () {
            Route::post('/register', [AuthController::class, 'registerSecretary']);
            Route::get('/', [AdminController::class, 'viewSecretaries']);
            Route::get('/{id}', [AdminController::class, 'viewSecretary']);
            Route::patch('/{id}/status', [AdminController::class, 'updateSecretary']);
            Route::delete('/{id}', [AdminController::class, 'deleteSecretary']);
        });

        // Patients
        Route::prefix('patients')->group(function () {
            Route::get('/', [AdminController::class, 'viewPatients']);
            Route::get('/{id}', [AdminController::class, 'viewPatient']);
            Route::patch('/{id}', [AdminController::class, 'updatePatient']);
            Route::delete('/{id}', [AdminController::class, 'deletePatient']);
        });
        //laboratory
        Route::prefix('labs')->group(function () {
            Route::post('/register', [AuthController::class, 'labRegister']);
                Route::get('/', [AdminController::class, 'viewLaboratories']);
                Route::get('/{id}', [AdminController::class, 'viewLaboratory']);
                Route::patch('/{id}/status', [AdminController::class, 'updateLaboratoryStatus']);
                Route::delete('/{id}', [AdminController::class, 'deleteLaboratory']);
            });
        });
    });

    // Doctor Routes
    Route::middleware(['auth:sanctum','role:doctor','active' ])->prefix('doctor')->group(function () {
        Route::get('lab-requests', [DoctorLabRequestController::class, 'store']);
        Route::get('lab-requests/{labRequest}', [DoctorLabRequestController::class, 'show']);
    });

    // Laboratory Routes
    Route::middleware(['auth:sanctum','role:laboratory','active'] )->prefix('laboratory')->group(function () {
        Route::get('/profile', [LabTechnicianController::class, 'viewProfile']);
        Route::post('/update', [LabTechnicianController::class, 'updateProfile']);
        Route::get('pending-requests', [LabTechnicianController::class, 'index']);
        Route::post('submit-results/{labRequest}', [LabTechnicianController::class, 'submitResults']);
    });

Route::middleware('auth:sanctum')->get('/test-auth', [AuthController::class, 'testAuth']);

