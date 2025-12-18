<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PickupController;
use App\Http\Controllers\DeliveryBoyController;
use App\Http\Controllers\AdminInventoryController;
use App\Http\Controllers\SupplyOrderController;

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
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}/track', [OrderController::class, 'track']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    // Cart Routes
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/', [CartController::class, 'store']);
        Route::put('/{id}', [CartController::class, 'update']);
        Route::delete('/{id}', [CartController::class, 'destroy']);
        Route::delete('/', [CartController::class, 'clear']);
    });

    // Favorites Routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('/', [FavoriteController::class, 'store']);
        Route::delete('/{productId}', [FavoriteController::class, 'destroy']);
    });

    // Address Routes
    Route::prefix('addresses')->group(function () {
        Route::get('/', [AddressController::class, 'index']);
        Route::post('/', [AddressController::class, 'store']);
        Route::put('/{id}', [AddressController::class, 'update']);
        Route::delete('/{id}', [AddressController::class, 'destroy']);
    });

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

    // Admin Inventory Routes
    Route::prefix('admin/inventory')->group(function () {
        Route::get('/summary', [AdminInventoryController::class, 'summary']);
        Route::get('/supply-orders', [AdminInventoryController::class, 'supplyOrders']);
        Route::post('/supply-orders/{id}/status', [AdminInventoryController::class, 'updateSupplyStatus']);
        Route::post('/allocate', [AdminInventoryController::class, 'allocateToVendor']);
        Route::get('/allocations', [AdminInventoryController::class, 'vendorAllocations']);
    });

    // Logout (available for all authenticated users)
    Route::post('/logout', [AuthController::class, 'logout']);
});


