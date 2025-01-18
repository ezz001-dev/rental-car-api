<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CarController;
use App\Http\Controllers\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('register', [AuthController::class, 'registerCustomer']);
Route::post('register/admin', [AuthController::class, 'registerAdmin']);

Route::get('/cars', [CarController::class , 'index']);
Route::get('/cars/search', [CarController::class, 'search']);
