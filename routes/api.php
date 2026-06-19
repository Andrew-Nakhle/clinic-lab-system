<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OtpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\registerPatient;
use App\Http\Controllers\registerDoORSe;
use App\Http\Controllers\loginController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\AdminController;
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
    //Route::post('logout',[AuthController::class,'logout'])->middleware('auth:sanctum');

});
Route::group(['api'],function(){
    Route::post('verifyOtp',[otpController::class,'verifyLoginOtp']);
    Route::post('resendOtp',[otpController::class,'resendLoginOtp']);
});
//superAdmin/////
Route::middleware(['auth:sanctum','permission:create_admins|update_admin|view_admins|delete_admin'])->group(function () {
    Route::post('/auth/register/admin', [AuthController::class, 'registerAdmin']);
    Route::patch( '/admin/{id}/status', [SuperAdminController::class, 'update']);
    Route::get('/admins', [SuperAdminController::class, 'viewAdmins']);
    Route::delete('/admin/{id}', [SuperAdminController::class, 'destroy']);

});
/////ADMIN///////
Route::middleware(['auth:sanctum','role:admin'])->group(function () {
Route::post('/auth/register/doctor', [AuthController::class, 'registerDoctor']);
Route::patch( '/doctor/{id}/status', [AdminController::class, 'updateDoctor']);
Route::get('/doctors', [AdminController::class, 'viewDoctors']);
Route::get('/doctor/{id}', [AdminController::class, 'viewDoctor']);
Route::delete('/doctor/{id}', [AdminController::class, 'delete']);
});

