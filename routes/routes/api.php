<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductionOrderController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SaleController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Authentication
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
    Route::post('/auth/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('auth.logout');
    Route::get('/auth/me', [AuthController::class, 'me'])
        ->middleware('auth:sanctum')
        ->name('auth.me');

    // Protected routes
    Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

        // Products
        Route::apiResource('products', ProductController::class);
        Route::get('products/{product}/inventory', [ProductController::class, 'inventory']);
        Route::get('products/{product}/kardex', [ProductController::class, 'kardex']);
        Route::get('products/{product}/recipes', [ProductController::class, 'recipes']);

        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Inventory
        Route::get('inventory', [InventoryController::class, 'index']);
        Route::get('inventory/{product}', [InventoryController::class, 'show']);
        Route::post('inventory/adjust', [InventoryController::class, 'adjust']);
        Route::post('inventory/transfer', [InventoryController::class, 'transfer']);
        Route::get('inventory/low-stock', [InventoryController::class, 'lowStock']);

        // Warehouses
        Route::apiResource('warehouses', WarehouseController::class);

        // Production
        Route::apiResource('production-orders', ProductionOrderController::class);
        Route::post('production-orders/{order}/plan', [ProductionOrderController::class, 'plan']);
        Route::post('production-orders/{order}/start', [ProductionOrderController::class, 'start']);
        Route::post('production-orders/{order}/finish', [ProductionOrderController::class, 'finish']);
        Route::post('production-orders/{order}/cancel', [ProductionOrderController::class, 'cancel']);
        Route::post('production-orders/{order}/register-production', [ProductionOrderController::class, 'registerProduction']);

        // Purchases
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        Route::post('purchase-orders/{order}/send', [PurchaseOrderController::class, 'send']);
        Route::post('purchase-orders/{order}/receive', [PurchaseOrderController::class, 'receive']);
        Route::post('purchase-orders/{order}/cancel', [PurchaseOrderController::class, 'cancel']);

        // Suppliers
        Route::apiResource('suppliers', SupplierController::class);

        // Sales
        Route::apiResource('sales', SaleController::class);
        Route::post('sales/{sale}/confirm', [SaleController::class, 'confirm']);
        Route::post('sales/{sale}/invoice', [SaleController::class, 'invoice']);
        Route::post('sales/{sale}/payment', [SaleController::class, 'registerPayment']);
        Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel']);

        // Customers
        Route::apiResource('customers', CustomerController::class);

        // Reports
        Route::get('reports/dashboard', [ReportController::class, 'dashboard']);
        Route::get('reports/sales', [ReportController::class, 'sales']);
        Route::get('reports/purchases', [ReportController::class, 'purchases']);
        Route::get('reports/inventory', [ReportController::class, 'inventory']);
        Route::get('reports/production', [ReportController::class, 'production']);
        Route::get('reports/profitability', [ReportController::class, 'profitability']);
    });
});
