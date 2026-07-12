<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\SecretaryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReportController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//Route::post('registerPatient',[registerPatient::class,'register']);
//Route::post('login',[loginController::class,'login']);
//Route::post('registerDoORSe', [registerDoORSe::class, 'register'])
//    ->middleware(['auth:sanctum', 'permission:add_DoctorORSecretary']);

Route::group(['prefix'=>'auth'],function(){
    Route::post('login',[AuthController::class,'loginUser']);
    Route::post('login/manager',[AuthController::class,'loginManager']);
    Route::post('/register/patient', [AuthController::class, 'registerPatient']);
    Route::get('/profile', [AuthController::class, 'profile'])->middleware('auth:sanctum');
    //Route::post('/register/doctor', [AuthController::class, 'registerDoctor']);
    //Route::post('/register/secretary', [AuthController::class, 'registerSecretary']);
    Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

});
Route::group(['api'],function(){
    Route::post('verifyOtp',[otpController::class,'verifyLoginOtp']);
    Route::post('resendOtp',[otpController::class,'resendLoginOtp']);
});
<<<<<<< HEAD
/////////////////////////////////////////superAdmin///////////////////////////////////////////////////////////////
Route::middleware(['auth:sanctum','permission:create_admins|update_admin|view_admins|delete_admin'])->group(function () {
=======
//superAdmin/////
Route::middleware(['auth:sanctum','role:super_admin'])->group(function () {
>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
    Route::post('/auth/register/admin', [AuthController::class, 'registerAdmin']);
    Route::patch( '/admin/{id}/status', [SuperAdminController::class, 'update']);
    Route::get('/admins', [SuperAdminController::class, 'viewAdmins']);
    Route::delete('/admin/{id}', [SuperAdminController::class, 'destroy']);

});
<<<<<<< HEAD
//////////////////////////////////////ADMIN/////////////////////////
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {

    // ---------------------------
    // Doctors
    //---------------------------
    Route::prefix('doctors')->group(function () {
        Route::post('/register', [AuthController::class, 'registerDoctor']);
        Route::get('/', [AdminController::class, 'viewDoctors']);
        Route::get('/{id}', [AdminController::class, 'viewDoctor']);
        Route::patch('/{id}/status', [AdminController::class, 'updateDoctor']);
        Route::delete('/{id}', [AdminController::class, 'deleteDoctor']);
    });

    // ---------------------------
    // Secretaries
    // ---------------------------
    Route::prefix('secretaries')->group(function () {
        Route::post('/register', [AuthController::class, 'registerSecretary']);
        Route::get('/', [AdminController::class, 'viewSecretaries']);
        Route::get('/{id}', [AdminController::class, 'viewSecretary']);
        Route::patch('/{id}/status', [AdminController::class, 'updateSecretary']);
        Route::delete('/{id}', [AdminController::class, 'deleteSecretary']);
    });


    // ---------------------------
    // Patients
    // ---------------------------
    Route::prefix('patients')->group(function () {
        Route::get('/', [AdminController::class, 'viewPatients']);
        Route::get('/{id}', [AdminController::class, 'viewPatient']);
        Route::patch('/{id}', [AdminController::class, 'updatePatient']);
        Route::delete('/{id}', [AdminController::class, 'deletePatient']);
    });


=======
/////ADMIN///////
Route::middleware(['auth:sanctum','role:admin','active'])->group(function () {
Route::post('/auth/register/doctor', [AuthController::class, 'registerDoctor']);
Route::post('auth/register/secretary', [AuthController::class, 'registerSecretary']);
Route::patch( '/doctor/{id}/status', [AdminController::class, 'updateDoctor']);
Route::get('/doctors', [AdminController::class, 'viewDoctors']);
Route::get('/doctor/{id}', [AdminController::class, 'viewDoctor']);
Route::delete('/doctor/{id}', [AdminController::class, 'delete']);
>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
});
///////DOCTOR//////
Route::middleware(['auth:sanctum','role:doctor','active'])->group(function () {

<<<<<<< HEAD





=======
    Route::post('/profile',[DoctorController::class,'updateProfile']);
Route::put('/profile',[DoctorController::class,'updateProfile']);


Route::get('/viewAppointments/today', [DoctorController::class, 'todayAppointments']);
    Route::get('/viewAppointments/previous', [DoctorController::class, 'previousAppointments']);
    Route::get('/viewAppointments/upcoming', [DoctorController::class, 'upcomingAppointments']);

});
////// PATIENT //////
Route::middleware(['auth:sanctum','role:patient','active'])->group(function () {

    Route::post('/appointment/patient', [AppointmentController::class,'bookByPatient']);
    Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots']
    );

});

////// SECRETARY //////
Route::middleware(['auth:sanctum','role:secretary','active'])->group(function () {


    Route::get('/patients/search', [SecretaryController::class,'searchPatient']);

    Route::post('/appointment/secretary', [AppointmentController::class,'bookBySecretary']);

});
>>>>>>> 347058423acfaa612372eae2f94fca8a80374f55
