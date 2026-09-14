<?php

use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Expert\Auth\AuthController as ExpertAuthController;
use App\Http\Controllers\Api\Expert\Auth\EmailVerificationController as ExpertEmailVerificationController;
use App\Http\Controllers\Api\Expert\Auth\PasswordController as ExpertPasswordController;
use App\Http\Controllers\Api\User\Auth\EmailVerificationController as UserEmailVerificationController;
use App\Http\Controllers\Api\User\Auth\PasswordController as UserPasswordController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:5,1')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
});

Route::post('/forgot-password', [UserPasswordController::class, 'forgot'])
    ->middleware('throttle:user-password-forgot')
    ->name('auth.password.forgot');
Route::post('/reset-password', [UserPasswordController::class, 'reset'])
    ->middleware('throttle:user-password-reset')
    ->name('auth.password.reset');
Route::get('/email/verify/{id}/{hash}', [UserEmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:10,1'])
    ->name('auth.email.verify');

Route::middleware(['auth:sanctum', 'regular-user', 'abilities:user:access'])->group(function (): void {
    Route::get('/user', [AuthController::class, 'user'])->name('auth.user');
    Route::post('/email/verification-notification', [UserEmailVerificationController::class, 'send'])
        ->middleware('throttle:user-verification')
        ->name('auth.email.send');
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
});

Route::prefix('admin/auth')->name('admin.auth.')->group(function (): void {
    Route::post('/login', [AdminAuthController::class, 'login'])
        ->middleware('throttle:admin-login')
        ->name('login');

    Route::middleware(['auth:sanctum', 'admin', 'abilities:admin:access'])->group(function (): void {
        Route::get('/me', [AdminAuthController::class, 'me'])->name('me');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [AdminAuthController::class, 'logoutAll'])->name('logout-all');
    });
});

Route::prefix('expert/auth')->name('expert.auth.')->group(function (): void {
    Route::post('/register', [ExpertAuthController::class, 'register'])
        ->middleware('throttle:expert-registration')
        ->name('register');
    Route::post('/login', [ExpertAuthController::class, 'login'])
        ->middleware('throttle:expert-login')
        ->name('login');
    Route::post('/forgot-password', [ExpertPasswordController::class, 'forgot'])
        ->middleware('throttle:expert-password-forgot')
        ->name('password.forgot');
    Route::post('/reset-password', [ExpertPasswordController::class, 'reset'])
        ->middleware('throttle:expert-password-reset')
        ->name('password.reset');

    Route::get('/email/verify/{id}/{hash}', [ExpertEmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:10,1'])
        ->name('email.verify');

    Route::middleware(['auth:sanctum', 'expert', 'abilities:expert:access'])->group(function (): void {
        Route::get('/me', [ExpertAuthController::class, 'me'])->name('me');
        Route::post('/email/verification-notification', [ExpertEmailVerificationController::class, 'send'])
            ->middleware('throttle:expert-verification')
            ->name('email.send');
        Route::put('/password', [ExpertPasswordController::class, 'update'])->name('password.update');
        Route::post('/logout', [ExpertAuthController::class, 'logout'])->name('logout');
        Route::post('/logout-all', [ExpertAuthController::class, 'logoutAll'])->name('logout-all');
    });
});
