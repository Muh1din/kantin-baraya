<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Owner\MenuCategoryController;
use App\Http\Controllers\Owner\MenuController;
use App\Http\Controllers\Owner\IngredientController;
use App\Http\Controllers\Owner\IngredientStockMovementController;
use App\Http\Controllers\Owner\RecipeController;
use App\Http\Controllers\Cashier\PosController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');
Route::get('/menu/{token}', [HomeController::class, 'tableMenu'])
    ->name('menu.table');

// Login hanya untuk pengguna yang belum login
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'store'])
        ->name('login.store');
});

// Logout pengguna yang sudah login
Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role:owner'])->get('/owner/dashboard', [DashboardController::class, 'index'])->name('owner.dashboard');
Route::middleware(['auth', 'role:kasir'])->get('/kasir/dashboard', [DashboardController::class, 'index'])->name('kasir.dashboard');
Route::middleware(['auth', 'role:pelanggan'])->get('/pelanggan/dashboard', [DashboardController::class, 'index'])->name('pelanggan.dashboard');
Route::middleware(['auth', 'role:koki'])->get('/koki/dashboard', [DashboardController::class, 'index'])->name('koki.dashboard');

// Route::prefix('kasir/pos')->name('kasir.pos.')->middleware(['auth', 'role:kasir'])->group(function () {
//     Route::get('/', [PosController::class, 'index'])->name('index');
//     Route::get('/history', [PosController::class, 'history'])->name('history');
//     Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
//     Route::get('/sales/{sale}', [PosController::class, 'receipt'])->name('receipt');
// });

Route::prefix('owner')
    ->name('owner.')
    ->middleware(['auth', 'role:owner'])
    ->group(function () {

        // ==========================================
        // 1. DASHBOARD UTAMA
        // ==========================================
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ==========================================
        // 2. LAPORAN & ANALITIK
        // ==========================================
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/index', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
            Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        });

        // ==========================================
        // 3. MASTER DATA: KATEGORI, MENU, RESEP, MEJA
        // ==========================================

        // Kategori Menu
        Route::get('/menu-categories', [MenuCategoryController::class, 'index'])->name('menu-categories.index');
        Route::post('/menu-categories', [MenuCategoryController::class, 'store'])->name('menu-categories.store');
        Route::get('/menu-categories/{menuCategory}/edit', [MenuCategoryController::class, 'edit'])->name('menu-categories.edit');
        Route::put('/menu-categories/{menuCategory}', [MenuCategoryController::class, 'update'])->name('menu-categories.update');
        Route::patch('/menu-categories/{menuCategory}/status', [MenuCategoryController::class, 'toggleStatus'])->name('menu-categories.toggle-status');

        // Menu
        Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
        Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
        Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::patch('/menus/{menu}/status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');

        // Resep Menu
        Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
        Route::get('/recipes/{menu}', [RecipeController::class, 'show'])->name('recipes.show');
        Route::post('/recipes/{menu}/ingredients', [RecipeController::class, 'storeIngredients'])->name('recipes.ingredients.store');
        Route::put('/recipes/{menu}/ingredients/{menuIngredient}', [RecipeController::class, 'updateQuantity'])->name('recipes.ingredients.update');
        Route::delete('/recipes/{menu}/ingredients/{menuIngredient}', [RecipeController::class, 'destroyIngredient'])->name('recipes.ingredients.destroy');

        // Meja & QR Code
        Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
        Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
        Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
        Route::patch('/tables/{table}/status', [TableController::class, 'toggleStatus'])->name('tables.toggle-status');
        Route::get('/tables/{table}/qr/download', [TableController::class, 'downloadQr'])->name('tables.qr.download');
        Route::get('/tables/{table}/qr', [TableController::class, 'generateQr'])->name('tables.qr');

        // ==========================================
        // 4. STOK, PEMASOK & PENGADAAN
        // ==========================================

        // Bahan Baku
        Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
        Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
        Route::get('/ingredients/{ingredient}/edit', [IngredientController::class, 'edit'])->name('ingredients.edit');
        Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
        Route::patch('/ingredients/{ingredient}/status', [IngredientController::class, 'toggleStatus'])->name('ingredients.toggle-status');
        Route::post('/ingredients/stock-in', [IngredientStockMovementController::class, 'stockIn'])->name('ingredients.stock-in');
        Route::post('/ingredients/adjustment', [IngredientStockMovementController::class, 'adjustment'])->name('ingredients.adjustment');

        // Pemasok (Suppliers)
        // Route::resource('suppliers', SupplierController::class)->except(['show']);

        // // Pembelian Bahan (Purchases)
        // Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        // Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        // Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');
        // Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
        // Route::patch('/purchases/{purchase}/receive', [PurchaseController::class, 'receive'])->name('purchases.receive');

        // ==========================================
        // 5. MANAJEMEN SDM & LOYALTY
        // ==========================================

        // Kelola Karyawan / Pengguna
        // Route::get('/users', [UserController::class, 'index'])->name('users.index');
        // Route::post('/users', [UserController::class, 'store'])->name('users.store');
        // Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        // Route::patch('/users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // // Loyalty & Reward
        // Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
        // Route::post('/rewards', [RewardController::class, 'store'])->name('rewards.store');
        // Route::put('/rewards/{reward}', [RewardController::class, 'update'])->name('rewards.update');
        // Route::patch('/rewards/{reward}/status', [RewardController::class, 'toggleStatus'])->name('rewards.toggle-status');

        // ==========================================
        // 6. PENGATURAN & AUDIT SISTEM
        // ==========================================

        // Pengaturan Usaha & Jam Operasional
        // Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        // Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        // Log Aktivitas
        // Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
;
