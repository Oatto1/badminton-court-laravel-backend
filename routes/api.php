<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\CourtController;
// use App\Http\Controllers\Api\MembershipController;
// use App\Http\Controllers\Api\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::post('/auth/register', [AuthController::class , 'register']);
Route::post('/auth/login', [AuthController::class , 'login']);
Route::get('/courts', [CourtController::class , 'index']);
Route::get('/availability', [BookingController::class , 'availability']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
            return $request->user();
        }
        );
        Route::get('/auth/me', function (Request $request) {
            return $request->user();
        }
        );


        // Booking
        Route::post('/bookings', [BookingController::class , 'store']);
        Route::post('/booking/hold', [BookingController::class , 'store']); // Match frontend call
        Route::get('/bookings', [BookingController::class , 'index']);
        Route::get('/bookings/{booking}', [BookingController::class , 'show']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class , 'cancel']);

        // Orders
        Route::get('/orders', [App\Http\Controllers\Api\OrderController::class , 'index']);
        Route::post('/orders', [App\Http\Controllers\Api\OrderController::class , 'store']);
        Route::get('/orders/{order}', [App\Http\Controllers\Api\OrderController::class , 'show']);

        // Payments (Mock)
        Route::post('/payments/confirm', [App\Http\Controllers\Api\PaymentController::class , 'mockSuccess']);
    });
