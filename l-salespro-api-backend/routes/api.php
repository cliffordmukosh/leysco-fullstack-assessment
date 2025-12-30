<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\DashboardController;

// API v1 - versioned endpoints
Route::prefix('v1')->group(function () {

    /**
     * Public Authentication Routes (rate-limited)
     */
    Route::prefix('auth')->middleware('throttle:auth')->group(function () {
        Route::post('login',           [AuthController::class, 'login']);
        Route::post('password/forgot', [AuthController::class, 'forgotPassword']);
        Route::post('password/reset',  [AuthController::class, 'resetPassword']);
    });

    /**
     * Protected Routes (require Sanctum token)
     */
    Route::middleware('auth:sanctum')->group(function () {

        // Auth-protected endpoints
        Route::prefix('auth')->group(function () {
            Route::post('logout',  [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::get('user',     [AuthController::class, 'user']);
        });

        /**
         * Inventory / Products
         */
        Route::prefix('products')->group(function () {
            Route::get('/',            [ProductController::class, 'index']);   
            Route::get('/low-stock',   [ProductController::class, 'lowStock']);              
            Route::get('/{id}',        [ProductController::class, 'show']);                     // Product details
            Route::post('/',           [ProductController::class, 'store'])->middleware('can:manage_inventory');
            Route::put('/{id}',        [ProductController::class, 'update'])->middleware('can:manage_inventory');
            Route::delete('/{id}',     [ProductController::class, 'destroy'])->middleware('can:manage_inventory');
            Route::get('/{id}/stock',  [ProductController::class, 'stock']);                     // Real-time stock
            Route::post('/{id}/reserve', [ProductController::class, 'reserve']);                // Reserve stock
            Route::post('/{id}/release', [ProductController::class, 'release']);                // Release reserved stock
        });

        /**
         * Warehouse Management
         */
        Route::prefix('warehouses')->group(function () {
            Route::get('/',               [WarehouseController::class, 'index'])
                 ->middleware('can:view_inventory');   // View warehouse list

            Route::get('/{id}/inventory', [WarehouseController::class, 'inventory'])
                 ->middleware('can:view_inventory');  // Warehouse stock details
        });

        // Stock transfers
        Route::prefix('stock-transfers')->group(function () {
            Route::post('/', [WarehouseController::class, 'storeTransfer'])
                 ->middleware('can:manage_inventory');  // Create transfer

            Route::get('/',  [WarehouseController::class, 'transferHistory'])
                 ->middleware('can:view_inventory');    // Transfer history
        });

        /**
         * Customer Management
         */
        Route::prefix('customers')->group(function () {
            Route::get('/',                 [CustomerController::class, 'index'])->middleware('can:view_customers');    // List customers
            Route::get('/map-data',         [CustomerController::class, 'mapData'])->middleware('can:view_customers');    // Mapping locations
            Route::get('/{id}',             [CustomerController::class, 'show'])->middleware('can:view_customers');     // Customer details
            Route::post('/',                [CustomerController::class, 'store'])->middleware('can:manage_customers');   // Create customer
            Route::put('/{id}',             [CustomerController::class, 'update'])->middleware('can:manage_customers');  // Update customer
            Route::delete('/{id}',          [CustomerController::class, 'destroy'])->middleware('can:manage_customers'); // Soft delete
            Route::get('/{id}/orders',      [CustomerController::class, 'orders'])->middleware('can:view_customers');    // Customer order history
            Route::get('/{id}/credit-status',[CustomerController::class, 'creditStatus'])->middleware('can:view_customers'); // Credit info
            Route::get('/map-data',         [CustomerController::class, 'mapData'])->middleware('can:view_customers');    // Mapping locations
        });

        /**
         * Sales Order Management
         */
        Route::prefix('orders')->group(function () {
            Route::get('/',                  [OrderController::class, 'index'])->middleware('can:view_orders');         // List orders
            Route::get('/{id}',              [OrderController::class, 'show'])->middleware('can:view_orders');          // Order details
            Route::post('/',                 [OrderController::class, 'store'])->middleware(['can:create_orders', 'check.credit.limit']);  // Create order
            Route::put('/{id}/status',       [OrderController::class, 'updateStatus'])->middleware('can:update_order_status');   // Update status
            Route::get('/{id}/invoice',      [OrderController::class, 'invoice'])->middleware('can:view_orders');        // Generate invoice
            Route::post('/calculate-total',  [OrderController::class, 'calculateTotal'])->middleware('can:create_orders'); // Calculate total
        });

        /**
         * Notifications
         */
        Route::prefix('notifications')->group(function () {
            Route::get('/',                  [NotificationController::class, 'index'])->name('notifications.index');          // List notifications
            Route::get('/unread-count',      [NotificationController::class, 'unreadCount'])->name('notifications.unread-count'); // Unread count
            Route::put('/{id}/read',         [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');      // Mark single read
            Route::put('/read-all',           [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read'); // Mark all read
            Route::delete('/{id}',           [NotificationController::class, 'destroy'])->name('notifications.destroy');       // Delete notification
        });

        /**
         * Dashboard Analytics
         */
        Route::prefix('dashboard')->group(function () {
            Route::get('/summary',           [DashboardController::class, 'summary'])->name('dashboard.summary');                   // Summary
            Route::get('/sales-performance', [DashboardController::class, 'salesPerformance'])->name('dashboard.sales-performance'); // Sales performance
            Route::get('/inventory-status',  [DashboardController::class, 'inventoryStatus'])->name('dashboard.inventory-status');   // Inventory overview
            Route::get('/top-products',      [DashboardController::class, 'topProducts'])->name('dashboard.top-products');           // Top selling products
        });
    });

    /**
     * Public password reset placeholder
     */
    Route::get('password/reset/{token}', function () {
        return response()->json([
            'message' => 'This is a placeholder. Use POST /api/v1/auth/password/reset to reset password.'
        ]);
    })->name('password.reset');
});
