<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Owner\TableController;
use App\Http\Controllers\Owner\ReportController;
use App\Http\Controllers\Owner\MenuCategoryController;
use App\Http\Controllers\Owner\MenuController;
use App\Http\Controllers\Owner\IngredientController;
use App\Http\Controllers\Owner\IngredientStockMovementController;
use App\Http\Controllers\Owner\RecipeController;
use App\Http\Controllers\Owner\UserController;
use App\Http\Controllers\Owner\OperationalHourController;
use App\Http\Controllers\Owner\HolidayController;
use App\Http\Controllers\Owner\CustomerController as OwnerCustomerController;
use App\Http\Controllers\Owner\VoucherController;
use App\Http\Controllers\Owner\RewardController;
use App\Http\Controllers\Owner\MenuLoyaltyPointController;
use App\Http\Controllers\Cashier\CashierDashboardController;
use App\Http\Controllers\Cashier\CustomerController as CashierCustomerController;
use App\Http\Controllers\Cashier\TransactionReportController;
use App\Http\Controllers\Cashier\MenuSalesReportController;
use App\Http\Controllers\Cashier\InventoryController as CashierInventoryController;
use App\Http\Controllers\Cashier\OrderController as CashierOrderController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Chef\ChefDashboardController;
use App\Http\Controllers\Customer\CustomerController;
use App\Http\Controllers\Customer\LoyaltyController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\MenuCatalogController;
use App\Http\Controllers\Guest\GuestOrderController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboardController;

// PUBLIC & AUTHENTICATION

// Route::get('/', [HomeController::class, 'index'])
//     ->name('home');

// Route::get('/menu/{token}', [HomeController::class, 'tableMenu'])
//     ->name('menu.table');

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'index'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'store'])
        ->name('login.store');

    Route::get('/register', [AuthController::class, 'registrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');


// OWNER
Route::prefix('owner')
    ->name('owner.')
    ->middleware(['auth', 'role:owner'])
    ->group(function () {


        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('dashboard');

        // Employee account management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->whereNumber('user')->name('users.destroy');

        // Customer accounts and their existing order history
        Route::get('/customers', [OwnerCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [OwnerCustomerController::class, 'show'])->whereNumber('customer')->name('customers.show');

        // Discount voucher management
        Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
        Route::post('/vouchers', [VoucherController::class, 'store'])->name('vouchers.store');
        Route::get('/vouchers/{voucher}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
        Route::put('/vouchers/{voucher}', [VoucherController::class, 'update'])->name('vouchers.update');
        Route::delete('/vouchers/{voucher}', [VoucherController::class, 'destroy'])->name('vouchers.destroy');

        // Free-menu rewards redeemed with customer points
        Route::get('/rewards', [RewardController::class, 'index'])->name('rewards.index');
        Route::post('/rewards', [RewardController::class, 'store'])->name('rewards.store');
        Route::get('/rewards/{reward}/edit', [RewardController::class, 'edit'])->name('rewards.edit');
        Route::put('/rewards/{reward}', [RewardController::class, 'update'])->name('rewards.update');
        Route::delete('/rewards/{reward}', [RewardController::class, 'destroy'])->name('rewards.destroy');
        Route::get('/rewards/menu-points', [MenuLoyaltyPointController::class, 'index'])->name('rewards.menu-points.index');
        Route::put('/rewards/menu-points/settings', [MenuLoyaltyPointController::class, 'update'])->name('rewards.menu-points.update');

        // Operational hours and holidays
        Route::get('/operational-hours', [OperationalHourController::class, 'index'])->name('operational-hours.index');
        Route::put('/operational-hours', [OperationalHourController::class, 'update'])->name('operational-hours.update');
        Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');
        Route::post('/holidays', [HolidayController::class, 'store'])->name('holidays.store');
        Route::delete('/holidays/{holiday}', [HolidayController::class, 'destroy'])->whereNumber('holiday')->name('holidays.destroy');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
            Route::get('/transactions', [ReportController::class, 'transactions'])->name('transactions');
            Route::get('/menu-sales', [ReportController::class, 'menuSales'])->name('menu-sales');
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

        Route::get('/inventory', [CashierInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [CashierInventoryController::class, 'store'])->name('inventory.store');
        Route::get('/inventory/stock-in', [CashierInventoryController::class, 'stockInForm'])->name('inventory.stock-in.form');
        Route::get('/inventory/stock-out', [CashierInventoryController::class, 'stockOutForm'])->name('inventory.stock-out.form');
        Route::get('/inventory/history', [CashierInventoryController::class, 'history'])->name('inventory.history');
        Route::get('/inventory/{ingredient}/edit', [CashierInventoryController::class, 'edit'])->whereNumber('ingredient')->name('inventory.edit');
        Route::put('/inventory/{ingredient}', [CashierInventoryController::class, 'update'])->whereNumber('ingredient')->name('inventory.update');
        Route::post('/inventory/stock-in', [CashierInventoryController::class, 'stockIn'])->name('inventory.stock-in');
        Route::post('/inventory/stock-out', [CashierInventoryController::class, 'stockOut'])->name('inventory.stock-out');

        // Customer directory and transaction history are read-only.
        Route::get('/customers', [CashierCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{customer}', [CashierCustomerController::class, 'show'])
            ->whereNumber('customer')
            ->name('customers.show');

        Route::get('/reports/transactions', [TransactionReportController::class, 'index'])
            ->name('reports.transactions');
        Route::get('/reports/menu-sales', [MenuSalesReportController::class, 'index'])
            ->name('reports.menu-sales');

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

            // Pesanan Berjalan
            Route::get('/berjalan', [CashierOrderController::class, 'processing'])
                ->name('processing');

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

        Route::get('/orders', [CustomerController::class, 'orders'])
            ->name('orders.index');

        Route::get('/loyalty', [LoyaltyController::class, 'index'])
            ->name('loyalty.index');
        Route::post('/loyalty/rewards/{reward}/redeem', [LoyaltyController::class, 'redeem'])
            ->whereNumber('reward')
            ->name('loyalty.redeem');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
            ->name('notifications.read');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::post('/orders', [CustomerController::class, 'storeOrder'])->name('orders.store');
        Route::post('/orders/{token}/payment-proof', [CustomerController::class, 'uploadPaymentProof'])
            ->name('orders.payment-proof');
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
