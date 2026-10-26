<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Home\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\Owner\MenuCategoryController;
use App\Http\Controllers\Owner\MenuController;
use App\Http\Controllers\Owner\IngredientController;
use App\Http\Controllers\Owner\IngredientStockMovementController;
use App\Http\Controllers\Owner\RecipeController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Chef\ChefDashboardController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\MenuCatalogController;
use App\Http\Controllers\Guest\GuestOrderController;


// PUBLIC & AUTHENTICATION

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

Route::get('/menu/{token}', [HomeController::class, 'tableMenu'])
    ->name('menu.table');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'index'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'store'])
        ->name('login.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');


// OWNER
Route::prefix('owner')
    ->name('owner.')
    ->middleware(['auth', 'role:owner'])
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/index', [ReportController::class, 'index'])->name('index');
            Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
            Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
            Route::get('/purchases', [ReportController::class, 'purchases'])->name('purchases');
        });

        // Menu Categories
        Route::get('/menu-categories', [MenuCategoryController::class, 'index'])->name('menu-categories.index');
        Route::post('/menu-categories', [MenuCategoryController::class, 'store'])->name('menu-categories.store');
        Route::get('/menu-categories/{menuCategory}/edit', [MenuCategoryController::class, 'edit'])->name('menu-categories.edit');
        Route::put('/menu-categories/{menuCategory}', [MenuCategoryController::class, 'update'])->name('menu-categories.update');
        Route::patch('/menu-categories/{menuCategory}/status', [MenuCategoryController::class, 'toggleStatus'])->name('menu-categories.toggle-status');

        // Menus
        Route::get('/menus', [MenuController::class, 'index'])->name('menus.index');
        Route::post('/menus', [MenuController::class, 'store'])->name('menus.store');
        Route::get('/menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
        Route::put('/menus/{menu}', [MenuController::class, 'update'])->name('menus.update');
        Route::patch('/menus/{menu}/status', [MenuController::class, 'toggleStatus'])->name('menus.toggle-status');

        // Recipes
        Route::get('/recipes', [RecipeController::class, 'index'])->name('recipes.index');
        Route::get('/recipes/{menu}', [RecipeController::class, 'show'])->name('recipes.show');
        Route::post('/recipes/{menu}/ingredients', [RecipeController::class, 'storeIngredients'])->name('recipes.ingredients.store');
        Route::put('/recipes/{menu}/ingredients/{menuIngredient}', [RecipeController::class, 'updateQuantity'])->name('recipes.ingredients.update');
        Route::delete('/recipes/{menu}/ingredients/{menuIngredient}', [RecipeController::class, 'destroyIngredient'])->name('recipes.ingredients.destroy');

        // Tables & QR
        Route::get('/tables', [TableController::class, 'index'])->name('tables.index');
        Route::post('/tables', [TableController::class, 'store'])->name('tables.store');
        Route::put('/tables/{table}', [TableController::class, 'update'])->name('tables.update');
        Route::patch('/tables/{table}/status', [TableController::class, 'toggleStatus'])->name('tables.toggle-status');
        Route::get('/tables/{table}/qr/download', [TableController::class, 'downloadQr'])->name('tables.qr.download');
        Route::get('/tables/{table}/qr', [TableController::class, 'generateQr'])->name('tables.qr');

        // Ingredients & Stock
        Route::get('/ingredients', [IngredientController::class, 'index'])->name('ingredients.index');
        Route::post('/ingredients', [IngredientController::class, 'store'])->name('ingredients.store');
        Route::get('/ingredients/{ingredient}/edit', [IngredientController::class, 'edit'])->name('ingredients.edit');
        Route::put('/ingredients/{ingredient}', [IngredientController::class, 'update'])->name('ingredients.update');
        Route::patch('/ingredients/{ingredient}/status', [IngredientController::class, 'toggleStatus'])->name('ingredients.toggle-status');
        Route::post('/ingredients/stock-in', [IngredientStockMovementController::class, 'stockIn'])->name('ingredients.stock-in');
        Route::post('/ingredients/adjustment', [IngredientStockMovementController::class, 'adjustment'])->name('ingredients.adjustment');
    });


// CASHIER
Route::prefix('kasir')
    ->name('kasir.')
    ->middleware(['auth', 'role:kasir'])
    ->group(function () {

        Route::get('/dashboard', [CashierDashboardController::class, 'index'])
            ->name('dashboard');

        // POS
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('/', [PosController::class, 'index'])->name('index');
            Route::get('/history', [PosController::class, 'history'])->name('history');
            Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
            Route::get('/orders/{order}', [PosController::class, 'receipt'])->name('receipt');
        });

        // Order Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [CashierOrderController::class, 'index'])->name('index');
            Route::get('/{order}', [CashierOrderController::class, 'show'])->whereNumber('order')->name('show');
            Route::post('/{order}/confirm', [CashierOrderController::class, 'confirm'])->whereNumber('order')->name('confirm');
            Route::post('/{order}/reject', [CashierOrderController::class, 'reject'])->whereNumber('order')->name('reject');
            Route::post('/{order}/payments/{payment}/verify', [CashierOrderController::class, 'verifyPayment'])
                ->whereNumber('order')
                ->whereNumber('payment')
                ->name('payments.verify');
        });
    });


// CHEF
Route::prefix('chef')
    ->name('chef.')
    ->middleware(['auth', 'role:koki'])
    ->group(function () {

        Route::get('/dashboard', [ChefDashboardController::class, 'index'])
            ->name('dashboard');

        Route::post('/orders/{order}/start', [ChefDashboardController::class, 'startCooking'])
            ->name('orders.start');

        Route::post('/orders/{order}/complete', [ChefDashboardController::class, 'complete'])
            ->name('orders.complete');

        Route::get('/orders/check-new', [ChefDashboardController::class, 'checkNewOrders'])
            ->name('orders.check-new');
    });


// CUSTOMER
Route::prefix('customer')
    ->name('customer.')
    ->middleware(['auth', 'role:customer'])
    ->group(function () {

        Route::get('/dashboard', [CustomerController::class, 'index'])
            ->name('dashboard');

        Route::get('/scan-qr', [CustomerController::class, 'scanQr'])
            ->name('scan-qr');

        Route::get('/menus', [MenuCatalogController::class, 'index'])
            ->name('menus.index');
    });


// GUEST
Route::prefix('guest')
    ->name('guest.')
    ->group(function () {

        Route::post('/orders', [GuestOrderController::class, 'store'])
            ->name('orders.store');

        Route::post('/orders/{token}/payment-proof', [GuestOrderController::class, 'uploadPaymentProof'])
            ->name('orders.payment-proof');
    });
