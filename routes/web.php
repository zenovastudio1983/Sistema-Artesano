<?php

use Illuminate\Support\Facades\Route;

// Auth routes (ERP empleados)
require __DIR__ . '/auth.php';

// ─── Sitio público ArtEmAr ────────────────────────────────────────────────

// Landing
Route::view('/', 'storefront.home')->name('home');

// Páginas estáticas / contacto
Route::get('/quienes-somos', \App\Http\Livewire\Storefront\AboutPage::class)->name('about');
Route::get('/contacto', \App\Http\Livewire\Storefront\ContactPage::class)->name('contact');

// Auth compradores
Route::middleware('guest:buyer')->group(function () {
    Route::get('/acceso',   \App\Http\Livewire\Storefront\BuyerLogin::class)->name('buyer.login');
    Route::get('/registro', \App\Http\Livewire\Storefront\BuyerRegister::class)->name('buyer.register');
});

Route::post('/buyer/logout', function () {
    auth('buyer')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home');
})->name('buyer.logout');

// Catálogo (libre, sin auth)
Route::prefix('tienda')->name('tienda.')->group(function () {
    Route::get('/', \App\Http\Livewire\Storefront\Catalog::class)->name('index');
    Route::get('/{slug}', \App\Http\Livewire\Storefront\ProductDetail::class)->name('product');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', \App\Http\Livewire\Dashboard\DashboardMain::class)->name('dashboard');

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', \App\Http\Livewire\Products\ProductIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Products\ProductForm::class)->name('create');
        Route::get('/{product}/edit', \App\Http\Livewire\Products\ProductForm::class)->name('edit');
        Route::get('/{product}', \App\Http\Livewire\Products\ProductShow::class)->name('show');
    });

    // Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', \App\Http\Livewire\Products\CategoryIndex::class)->name('index');
    });

    // Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', \App\Http\Livewire\Inventory\InventoryIndex::class)->name('index');
        Route::get('/movements', \App\Http\Livewire\Inventory\MovementIndex::class)->name('movements');
        Route::get('/adjust', \App\Http\Livewire\Inventory\AdjustStock::class)->name('adjust');
        Route::get('/transfer', \App\Http\Livewire\Inventory\TransferStock::class)->name('transfer');
        Route::get('/kardex/{product}', \App\Http\Livewire\Inventory\KardexView::class)->name('kardex');
    });

    // Warehouses
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::get('/', \App\Http\Livewire\Inventory\WarehouseIndex::class)->name('index');
    });

    // Recipes
    Route::prefix('recipes')->name('recipes.')->group(function () {
        Route::get('/', \App\Http\Livewire\Recipes\RecipeIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Recipes\RecipeForm::class)->name('create');
        Route::get('/{recipe}/edit', \App\Http\Livewire\Recipes\RecipeForm::class)->name('edit');
        Route::get('/{recipe}', \App\Http\Livewire\Recipes\RecipeShow::class)->name('show');
    });

    // Production
    Route::prefix('production')->name('production.')->group(function () {
        Route::get('/', \App\Http\Livewire\Production\ProductionIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Production\ProductionForm::class)->name('create');
        Route::get('/{order}/edit', \App\Http\Livewire\Production\ProductionForm::class)->name('edit');
        Route::get('/{order}', \App\Http\Livewire\Production\ProductionShow::class)->name('show');
    });

    // Purchases
    Route::prefix('purchases')->name('purchases.')->group(function () {
        Route::get('/', \App\Http\Livewire\Purchases\PurchaseIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Purchases\PurchaseForm::class)->name('create');
        Route::get('/{order}/edit', \App\Http\Livewire\Purchases\PurchaseForm::class)->name('edit');
        Route::get('/{order}', \App\Http\Livewire\Purchases\PurchaseShow::class)->name('show');
        Route::get('/{order}/receive', \App\Http\Livewire\Purchases\ReceiveGoods::class)->name('receive');
    });

    // Suppliers
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/', \App\Http\Livewire\Purchases\SupplierIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Purchases\SupplierForm::class)->name('create');
        Route::get('/{supplier}/edit', \App\Http\Livewire\Purchases\SupplierForm::class)->name('edit');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', \App\Http\Livewire\Sales\SaleIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Sales\SaleForm::class)->name('create');
        Route::get('/{sale}/edit', \App\Http\Livewire\Sales\SaleForm::class)->name('edit');
        Route::get('/{sale}', \App\Http\Livewire\Sales\SaleShow::class)->name('show');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', \App\Http\Livewire\Sales\CustomerIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Sales\CustomerForm::class)->name('create');
        Route::get('/{customer}/edit', \App\Http\Livewire\Sales\CustomerForm::class)->name('edit');
    });

    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', \App\Http\Livewire\Reports\ReportsDashboard::class)->name('index');
        Route::get('/sales', \App\Http\Livewire\Reports\SalesReport::class)->name('sales');
        Route::get('/purchases', \App\Http\Livewire\Reports\PurchasesReport::class)->name('purchases');
        Route::get('/inventory', \App\Http\Livewire\Reports\InventoryReport::class)->name('inventory');
        Route::get('/production', \App\Http\Livewire\Reports\ProductionReport::class)->name('production');
        Route::get('/profitability', \App\Http\Livewire\Reports\ProfitabilityReport::class)->name('profitability');
    });

    // Users & Roles
    Route::prefix('users')->name('users.')->middleware('can:view users')->group(function () {
        Route::get('/', \App\Http\Livewire\Users\UserIndex::class)->name('index');
        Route::get('/create', \App\Http\Livewire\Users\UserForm::class)->name('create');
        Route::get('/{user}/edit', \App\Http\Livewire\Users\UserForm::class)->name('edit');
    });

    Route::prefix('roles')->name('roles.')->middleware('can:assign roles')->group(function () {
        Route::get('/', \App\Http\Livewire\Users\RoleIndex::class)->name('index');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->middleware('can:view settings')->group(function () {
        Route::get('/', \App\Http\Livewire\Users\SettingsPage::class)->name('index');
    });
});
