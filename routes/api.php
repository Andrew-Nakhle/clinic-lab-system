<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\registerPatient;
use App\Http\Controllers\registerDoORSe;
use App\Http\Controllers\loginController;
use App\Http\Controllers\SuperAdminController;
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
    Route::post('/register/doctor', [AuthController::class, 'registerDoctor']);
    Route::post('/register/secretary', [AuthController::class, 'registerSecretary']);
    Route::post('/register/admin', [AuthController::class, 'registerAdmin'])->middleware(['auth:sanctum','permission:register admin']);
    Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

});
Route::group(['api'],function(){
    Route::post('verifyOtp',[otpController::class,'verifyLoginOtp']);
    Route::post('resendOtp',[otpController::class,'resendLoginOtp']);
});
//superAdmin
Route::post('/super-admin', [AuthController::class, 'SuperAdmin']);
Route::middleware(['auth:sanctum', 'role:super_admin'])->group(function () {
    Route::get('/super-admin/doctors', [SuperAdminController::class, 'view_doctors']);
});

