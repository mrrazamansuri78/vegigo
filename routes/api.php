<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\DeliveryBoyController;

Route::prefix('auth')->group(function () {
    Route::post('/send-otp', [AuthController::class, 'sendOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/signup', [AuthController::class, 'signup']);
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth:simple_token')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);

    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/suggested', [ProductController::class, 'suggested']);
    });

    Route::get('/orders', [OrderController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::post('/pickups', [PickupController::class, 'store']);

    // Delivery Boy Routes
    Route::prefix('delivery')->group(function () {
        Route::get('/dashboard', [DeliveryBoyController::class, 'dashboard']);
        Route::get('/history', [DeliveryBoyController::class, 'history']);
        Route::get('/track', [DeliveryBoyController::class, 'track']);
        Route::get('/track/{orderId}', [DeliveryBoyController::class, 'track']);
        Route::post('/orders/{orderId}/accept', [DeliveryBoyController::class, 'acceptPickup']);
        Route::post('/orders/{orderId}/reject', [DeliveryBoyController::class, 'rejectPickup']);
        Route::post('/orders/{orderId}/picked-up', [DeliveryBoyController::class, 'markPickedUp']);
        Route::post('/orders/{orderId}/delivered', [DeliveryBoyController::class, 'markDelivered']);
        Route::get('/orders/{orderId}', [DeliveryBoyController::class, 'orderDetails']);
        Route::post('/location', [DeliveryBoyController::class, 'updateLocation']);
        Route::get('/profile', [DeliveryBoyController::class, 'getProfile']);
        Route::put('/profile', [DeliveryBoyController::class, 'updateProfile']);
        Route::put('/settings', [DeliveryBoyController::class, 'updateSettings']);
    });

    // Logout (available for all authenticated users)
    Route::post('/logout', [AuthController::class, 'logout']);
});


