<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\KostController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/kosts', [KostController::class, 'index']);
Route::get('/kosts/{kost}', [KostController::class, 'show']);

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', fn (Request $request) => $request->user());
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store']);
});
