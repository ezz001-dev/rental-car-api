<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CarController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\ReturnRentalController as ReturnController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::middleware(['cors'])->group(function(){

    Route::post('register', [AuthController::class, 'registerCustomer']);
    Route::post('register/admin', [AuthController::class, 'registerAdmin']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

    Route::get('/cars', [CarController::class , 'index']);
    Route::get('/cars/search', [CarController::class, 'search']);

    Route::post('/cars/add', [CarController::class, 'store'])->middleware(['auth:sanctum' , 'admin']);


    Route::middleware('auth:sanctum')->group(function () {

        // Peminjaman mobil
        Route::post('rentals', [RentalController::class, 'rentCar']);

        // Daftar mobil yang sedang disewa oleh pengguna
        Route::get('rentals/user', [RentalController::class, 'userRentals']);
    });


    Route::middleware('auth:sanctum')->group(function () {
        // Pengembalian mobil
        Route::post('returns', [ReturnController::class, 'returnCar']);

        // Daftar pengembalian mobil oleh pengguna
        Route::get('returns/user', [ReturnController::class, 'userReturns']);
    });

});
