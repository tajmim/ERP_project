<?php

use App\Http\Controllers\Developerauth\AuthenticatedSessionController;
use App\Http\Controllers\Developerauth\ConfirmablePasswordController;
use App\Http\Controllers\Developerauth\EmailVerificationNotificationController;
use App\Http\Controllers\Developerauth\EmailVerificationPromptController;
use App\Http\Controllers\Developerauth\NewPasswordController;
use App\Http\Controllers\Developerauth\PasswordController;
use App\Http\Controllers\Developerauth\PasswordResetLinkController;
use App\Http\Controllers\Developerauth\RegisteredUserController;
use App\Http\Controllers\Developerauth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['guest:developer'], 'prefix' => 'developer', 'as' => 'developer.'], function(){
    // code here

    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
                ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
});

Route::group(['middleware' => ['auth:developer'], 'prefix' => 'developer', 'as' => 'developer.'], function(){
    Route::get('verify-email', EmailVerificationPromptController::class)
                ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
                ->middleware(['signed', 'throttle:6,1'])
                ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
                ->middleware('throttle:6,1')
                ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
                ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
                ->name('logout');
});