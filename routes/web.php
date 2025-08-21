<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PublishScheduleController;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
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
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');

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

    Route::get('/dashoboard', [DashboardController::class, 'index'])->name('posts.index');
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}', [PostController::class, 'show'])->name('posts.show');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');

    // Publish Schedule Routes
    Route::resource('schedules', PublishScheduleController::class);
    
    // Rutas adicionales para funcionalidades AJAX
    Route::prefix('schedules')->name('schedules.')->group(function () {
        // Activar/desactivar horario
        Route::patch('{schedule}/toggle', [PublishScheduleController::class, 'toggle'])
            ->name('toggle');
        
        // Obtener horarios para un día específico
        Route::get('day/{day}', [PublishScheduleController::class, 'getSchedulesForDay'])
            ->name('day');
        
        // Clonar horarios de un día a otro
        Route::post('clone-day', [PublishScheduleController::class, 'cloneDay'])
            ->name('clone-day');
        
        // Obtener estadísticas en tiempo real
        Route::get('stats', [PublishScheduleController::class, 'getStats'])
            ->name('stats');
    });

    Route::post('/dashboard/queue/{queueId}/cancel', [DashboardController::class, 'cancel'])->name('posts.queue.cancel');
});
?>
