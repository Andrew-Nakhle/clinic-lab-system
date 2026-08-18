<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

use App\Http\Controllers\{AuthController,
    OtpController,
    PaymentController,
    SecretaryController,
    SuperAdminController,
    AdminController,
    DoctorController,
    AppointmentController,
    LabTechnicianController,
    DoctorLabRequestController,
    ChatController,
    PatientLabRequestController};

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'loginUser']);
    Route::post('login/manager', [AuthController::class, 'loginManager']);
    Route::post('register/patient', [AuthController::class, 'registerPatient']);
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('verifyOtp', [OtpController::class, 'verifyLoginOtp']);
Route::post('resendOtp', [OtpController::class, 'resendLoginOtp']);
//stripe
Route::post('/stripe/webhook', [PaymentController::class, 'webhook']);



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/test-auth', [AuthController::class, 'testAuth']);



    Route::get('/patients/', [AdminController::class, 'viewPatients'])->middleware('permission:view_patients','active');
    Route::get('/doctors/', [AdminController::class, 'viewDoctors'])->middleware('permission:view_doctors','active');

    Route::get('/medical-articles/category', [DoctorController::class, 'getArticlesByCategory'])->middleware('permission:get_articles_by_category','active');
    Route::get('/medical-articles/doctor/{doctor_id}', [DoctorController::class, 'getArticlesByDoctor'])->middleware('permission:get_articles_by_doctor','active');

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
        Route::post('/updateProfile', [AdminController::class, 'updateProfile']);
        Route::get('/viewProfile', [AdminController::class, 'viewProfile']);
        Route::prefix('doctors')->group(function () {
            Route::post('/register', [AuthController::class, 'registerDoctor']);
            Route::get('/viewDoctorsBySection', [AdminController::class, 'ViewDoctorsBySection']);

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

            Route::get('/{id}', [AdminController::class, 'viewPatient']);
            Route::patch('/{id}', [AdminController::class, 'updatePatient']);
            Route::delete('/{id}', [AdminController::class, 'deletePatient']);
        });

        Route::prefix('labs')->group(function () {
            Route::post('/register', [AuthController::class, 'labRegister']);
            Route::get('/', [AdminController::class, 'viewLaboratories']);
            Route::get('/{id}', [AdminController::class, 'viewLaboratory']);
            Route::put('/{id}/status', [AdminController::class, 'updateLaboratoryStatus']);
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
        Route::post('/lab-requests', [DoctorLabRequestController::class, 'store']);
        Route::get('lab-requests/{labRequest}', [DoctorLabRequestController::class, 'show']);
        Route::get('/medicalNotes/{id}', [DoctorController::class, 'getMedicalNotes']);
        Route::post('/completeAppointment', [DoctorController::class, 'completeAppointment']);
        Route::post('/createArticle', [DoctorController::class, 'createArticle']);
        Route::post('/medical-articles/{id}', [DoctorController::class, 'updateArticle']);
        Route::put('/medical-articles/{id}', [DoctorController::class, 'updateArticle']);
        Route::delete('/medical-articles/{id}', [DoctorController::class, 'deleteArticle']);

    });

    // Laboratory
    Route::middleware(['role:laboratory', 'active'])->prefix('laboratory')->group(function () {
        Route::get('/profile',[LabTechnicianController::class, 'viewProfile']);
        Route::post('/update', [LabTechnicianController::class, 'updateProfile']);
        Route::get('pending-requests', [LabTechnicianController::class, 'index']);
        Route::post('submit-results/{labRequest}', [LabTechnicianController::class, 'submitResults']);
    });

    // Patient
    Route::middleware(['role:patient', 'active'])->prefix('patient')->group(function () {
        Route::post('/appointment', [AppointmentController::class, 'bookByPatient']);
        Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']);
        Route::post('/updatePatientProfile', [AuthController::class, 'updatePatientProfile']);
        Route::put('/updatePatientProfile', [AuthController::class, 'updatePatientProfile']);
    });

    // Chat Routes
    Route::get('/chat/{receiverId}', [ChatController::class, 'index']);
    Route::post('/chat/send', [ChatController::class, 'sendMessage']);

    Route::middleware(['auth:sanctum'])->prefix('patient')->group(function () {
        Route::get('/medical-tests', [PatientLabRequestController::class, 'indexAvailableTests']);
        Route::get('/received', [PatientLabRequestController::class, 'receivedRequests']);
        Route::post('/store', [PatientLabRequestController::class, 'store']);
        Route::get('/pending', [PatientLabRequestController::class, 'pendingRequests']);
        Route::delete('/{labRequest}', [PatientLabRequestController::class, 'destroy']);
    });
    //secretary


    Route::middleware(['role:secretary', 'active'])->prefix('secretary')->group(function () {

        Route::post('/appointments/{appointment}/attend', [AppointmentController::class, 'markAsAttended']);
        Route::post('/appointments/{appointment}/no-show', [AppointmentController::class, 'markAsNoShow']);
        Route::get('/search/patient',[SecretaryController::class, 'searchPatient']);
        Route::post('/appointment',[AppointmentController::class, 'bookBySecretary']);
        });

});

