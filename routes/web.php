<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;



Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');


    // Two-Factor Authentication Routes
    Route::get('/security', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/toggle', [TwoFactorController::class, 'toggle'])->name('two-factor.toggle');
    Route::post('/two-factor/regenerate-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-codes');

});
?>
