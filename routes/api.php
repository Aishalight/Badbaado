<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReferralController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:healthcare_worker,referral_coordinator')->post(
        'referrals',
        [ReferralController::class, 'store']
    );

    Route::get('referrals', [ReferralController::class, 'index']);
    Route::get('referrals/{referral}', [ReferralController::class, 'show']);
    Route::post('referrals/{referral}/transition', [ReferralController::class, 'transition']);

    Route::get('referrals/{referral}/messages', [MessageController::class, 'index']);
    Route::post('referrals/{referral}/messages', [MessageController::class, 'store']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
});
