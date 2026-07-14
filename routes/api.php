<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\{
    AuthController, OtpController, SuperAdminController, AdminController,
    DoctorController, AppointmentController, LabTechnicianController,
    DoctorLabRequestController
};

/*
|--------------------------------------------------------------------------
| 1. المسارات العامة (Public Routes)
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
| 2. المسارات المحمية (Protected Routes)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // بيانات المستخدم العامة
    Route::get('/user', fn(Request $request) => $request->user());
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/test-auth', [AuthController::class, 'testAuth']);

    // Super Admin
    Route::middleware('role:super_admin')->prefix('super-admin')->group(function () {
        Route::prefix('admins')->group(function () {
            Route::get('/', [SuperAdminController::class, 'viewAdmins']);
            Route::post('/register', [AuthController::class, 'registerAdmin']);
            Route::get('/{id}', [SuperAdminController::class, 'viewAdmin']);
            Route::patch('/{id}/status', [SuperAdminController::class, 'update']);
            Route::delete('/{id}', [SuperAdminController::class, 'destroy']);
        });
        Route::prefix('sections')->group(function () {
            Route::get('/', [SuperAdminController::class, 'viewSections']);
            Route::patch('/{id}/price', [SuperAdminController::class, 'updateSectionPrice']);
        });
    });

    // Admin
    Route::middleware(['role:admin', 'active'])->group(function () {
        Route::get('/viewProfile', [AdminController::class, 'viewProfile']);
        Route::post('/updateProfile', [AdminController::class, 'updateProfile']);

        Route::prefix('doctors')->group(function () {
            Route::post('/register', [AuthController::class, 'registerDoctor']);
            Route::get('/', [AdminController::class, 'viewDoctors']);
            Route::get('/{id}', [AdminController::class, 'viewDoctor']);
            Route::patch('/{id}/status', [AdminController::class, 'updateDoctor']);
            Route::delete('/{id}', [AdminController::class, 'deleteDoctor']);
        });

        Route::prefix('secretaries')->group(function () {
            Route::post('/register', [AuthController::class, 'registerSecretary']);
            Route::get('/', [AdminController::class, 'viewSecretaries']);
            Route::get('/{id}', [AdminController::class, 'viewSecretary']);
            Route::patch('/{id}/status', [AdminController::class, 'updateSecretary']);
            Route::delete('/{id}', [AdminController::class, 'deleteSecretary']);
        });

        Route::prefix('patients')->group(function () {
            Route::get('/', [AdminController::class, 'viewPatients']);
            Route::get('/{id}', [AdminController::class, 'viewPatient']);
            Route::patch('/{id}', [AdminController::class, 'updatePatient']);
            Route::delete('/{id}', [AdminController::class, 'deletePatient']);
        });

        Route::prefix('labs')->group(function () {
            Route::post('/register', [AuthController::class, 'labRegister']);
            Route::get('/', [AdminController::class, 'viewLaboratories']);
            Route::get('/{id}', [AdminController::class, 'viewLaboratory']);
            Route::patch('/{id}/status', [AdminController::class, 'updateLaboratoryStatus']);
            Route::delete('/{id}', [AdminController::class, 'deleteLaboratory']);
        });
    });

    // Doctor
    Route::middleware(['role:doctor', 'active'])->prefix('doctor')->group(function () {
        Route::post('/profile', [DoctorController::class, 'updateProfile']);
        Route::put('/profile', [DoctorController::class, 'updateProfile']);
        Route::get('/viewAppointments/today', [DoctorController::class, 'todayAppointments']);
        Route::get('/viewAppointments/previous', [DoctorController::class, 'previousAppointments']);
        Route::get('/viewAppointments/upcoming', [DoctorController::class, 'upcomingAppointments']);
        Route::get('/medicalRecord', [DoctorController::class, 'getMedicalRecord']);
        Route::get('lab-requests', [DoctorLabRequestController::class, 'store']);
        Route::get('lab-requests/{labRequest}', [DoctorLabRequestController::class, 'show']);
    });

    // Laboratory
    Route::middleware(['role:laboratory', 'active'])->prefix('laboratory')->group(function () {
        Route::get('/profile', [LabTechnicianController::class, 'viewProfile']);
        Route::post('/update', [LabTechnicianController::class, 'updateProfile']);
        Route::get('pending-requests', [LabTechnicianController::class, 'index']);
        Route::post('submit-results/{labRequest}', [LabTechnicianController::class, 'submitResults']);
    });

    // Patient
    Route::middleware(['role:patient', 'active'])->prefix('patient')->group(function () {
        Route::post('/appointment', [AppointmentController::class, 'bookByPatient']);
        Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
    });
});
