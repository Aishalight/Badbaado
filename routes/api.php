<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AdminHospitalController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BackupController;
use App\Http\Controllers\Api\CmsContentController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\HospitalController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PlatformAlertController;
use App\Http\Controllers\Api\ReferralAttachmentController;
use App\Http\Controllers\Api\ReferralController;
use App\Http\Controllers\Api\SystemSettingController;
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

    Route::get('referrals/{referral}/attachments/{attachment}', [ReferralAttachmentController::class, 'download']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::get('hospitals', [HospitalController::class, 'index']);

    Route::middleware('role:hospital_admin,system_admin')->group(function () {
        Route::get('analytics/overview', [AnalyticsController::class, 'overview']);
        Route::get('admin/users', [AdminUserController::class, 'index']);
        Route::post('admin/users', [AdminUserController::class, 'store']);
        Route::patch('admin/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('admin/users/{user}', [AdminUserController::class, 'destroy']);
    });

    Route::middleware('role:system_admin')->group(function () {
        Route::get('admin/hospitals', [AdminHospitalController::class, 'index']);
        Route::post('admin/hospitals', [AdminHospitalController::class, 'store']);
        Route::patch('admin/hospitals/{hospital}', [AdminHospitalController::class, 'update']);
        Route::delete('admin/hospitals/{hospital}', [AdminHospitalController::class, 'destroy']);
        Route::get('admin/activity', [ActivityController::class, 'index']);
        Route::get('admin/cms', [CmsContentController::class, 'index']);
        Route::patch('admin/cms/{content}', [CmsContentController::class, 'update']);
        Route::get('admin/announcements', [AnnouncementController::class, 'index']);
        Route::post('admin/announcements', [AnnouncementController::class, 'store']);
        Route::patch('admin/announcements/{announcement}', [AnnouncementController::class, 'update']);
        Route::delete('admin/announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        Route::get('admin/faqs', [FaqController::class, 'index']);
        Route::post('admin/faqs', [FaqController::class, 'store']);
        Route::patch('admin/faqs/{faq}', [FaqController::class, 'update']);
        Route::delete('admin/faqs/{faq}', [FaqController::class, 'destroy']);
        Route::patch('admin/settings', [SystemSettingController::class, 'update']);
        Route::post('admin/backups', [BackupController::class, 'store']);
        Route::delete('admin/backups/{backup}', [BackupController::class, 'destroy']);
        Route::post('admin/alerts', [PlatformAlertController::class, 'store']);
    });
});
