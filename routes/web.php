<?php
//
//use Illuminate\Support\Facades\Route;
//
//Route::get('/', function () {
//    return view('welcome');
//});


use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'status' => true,
        'message' => 'Clinic Lab API is running successfully'
    ]);
});
