<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminInventoryController;
use App\Http\Controllers\Admin\AdminSupplyController;

Route::get('/', function () {
    return view('welcome');
});

// Admin Routes
Route::prefix('admin')->group(function () {
    // Auth Routes
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Protected Admin Routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/api/live-orders', [AdminDashboardController::class, 'getLiveOrders'])->name('admin.api.live-orders');

        // Products
        Route::resource('products', AdminProductController::class)->names([
            'index' => 'admin.products.index',
            'create' => 'admin.products.create',
            'store' => 'admin.products.store',
            'edit' => 'admin.products.edit',
            'update' => 'admin.products.update',
            'destroy' => 'admin.products.destroy',
        ]);

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('admin.orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.update-status');

        // Inventory
        Route::get('/inventory', [AdminInventoryController::class, 'index'])->name('admin.inventory.index');
        Route::post('/inventory/supply/{id}/status', [AdminInventoryController::class, 'updateSupplyStatus'])->name('admin.inventory.supply.status');
        Route::post('/inventory/allocate', [AdminInventoryController::class, 'allocateToVendor'])->name('admin.inventory.allocate');

        // Farmer Supply Requests
        Route::get('/supply', [AdminSupplyController::class, 'index'])->name('admin.supply.index');
        Route::post('/supply/{pickupRequest}/status', [AdminSupplyController::class, 'updateStatus'])->name('admin.supply.update-status');
    });
});
