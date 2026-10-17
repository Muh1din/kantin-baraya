<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Pelanggan\PelangganDashboardController;
use App\Http\Controllers\Kaisr\KasirDashboardController;
use App\Http\Controllers\Owner\OwnerDashboardController;
use App\Http\Controllers\Home\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])
    ->name('home');

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


// Dashboard Owner
Route::middleware(['auth', 'role:owner'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function () {
        Route::get('/dashboard', [OwnerDashboardController::class, 'index'])
            ->name('dashboard');
    });

// Dashboard Kasir
Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        Route::get('/dashboard', [KasirDashboardController::class, 'index'])
            ->name('dashboard');
    });

// Dashboard Pelanggan
Route::middleware(['auth', 'role:pelanggan'])
    ->prefix('pelanggan')
    ->name('pelanggan.')
    ->group(function () {
        Route::get('/dashboard', [
            PelangganDashboardController::class,
            'index',
        ])->name('dashboard');
    });