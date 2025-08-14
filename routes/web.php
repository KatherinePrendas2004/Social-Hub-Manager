<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SocialAuthController;


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


    Route::get('/social', [SocialAuthController::class, 'index'])->name('social.index');

    Route::get('/social/redirect/{platform}', [SocialAuthController::class, 'redirectToProvider'])
        ->name('social.redirect')
        ->where('platform', 'twitter|linkedin|reddit');

    Route::get('/social/callback/{platform}', [SocialAuthController::class, 'handleProviderCallback'])
        ->name('social.callback')
        ->where('platform', 'twitter|linkedin|reddit');

    Route::post('/social/disconnect/{platform}', [SocialAuthController::class, 'disconnect'])
        ->name('social.disconnect')
        ->where('platform', 'twitter|linkedin|reddit');

    // Two-Factor Authentication Routes
    Route::get('/security', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor/toggle', [TwoFactorController::class, 'toggle'])->name('two-factor.toggle');
    Route::post('/two-factor/regenerate-codes', [TwoFactorController::class, 'regenerateRecoveryCodes'])->name('two-factor.regenerate-codes');

});
?>
