<?php

use App\Http\Controllers\AdminPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LoginPageController;
use App\Http\Controllers\NotificationPageController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ReferralPageController;
use App\Http\Controllers\SessionAuthController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');
Route::get('/how-it-works', [PageController::class, 'howItWorks'])->name('how-it-works');
Route::get('/features', [PageController::class, 'features'])->name('features');
Route::get('/about', [PageController::class, 'about'])->name('about');

Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPageController::class)->name('login');
    Route::post('/login', [SessionAuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [SessionAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');

    Route::get('/referrals', [ReferralPageController::class, 'index'])->name('referrals.index');
    Route::get('/referrals/new', [ReferralPageController::class, 'create'])->name('referrals.create');
    Route::get('/referrals/{referral}', [ReferralPageController::class, 'show'])->name('referrals.show');

    Route::get('/notifications', NotificationPageController::class)->name('notifications.index');

    Route::middleware('role:hospital_admin,system_admin')->group(function () {
        Route::get('/admin/analytics', [AdminPageController::class, 'analytics'])->name('admin.analytics');
        Route::get('/admin/users', [AdminPageController::class, 'users'])->name('admin.users');
    });

    Route::middleware('role:system_admin')->group(function () {
        Route::get('/admin', [AdminPageController::class, 'command'])->name('admin.command');
        Route::get('/admin/hospitals', [AdminPageController::class, 'hospitals'])->name('admin.hospitals');
        Route::get('/admin/referrals', [AdminPageController::class, 'referrals'])->name('admin.referrals');
        Route::get('/admin/security', [AdminPageController::class, 'security'])->name('admin.security');
        Route::get('/admin/audit', [AdminPageController::class, 'audit'])->name('admin.audit');
        Route::get('/admin/health', [AdminPageController::class, 'health'])->name('admin.health');
        Route::get('/admin/cms', [AdminPageController::class, 'cms'])->name('admin.cms');
        Route::get('/admin/config', [AdminPageController::class, 'config'])->name('admin.config');
        Route::get('/admin/reports', [AdminPageController::class, 'reports'])->name('admin.reports');
        Route::get('/admin/reports/export/{report}', [AdminPageController::class, 'reportExport'])->name('admin.reports.export');
        Route::get('/admin/backups', [AdminPageController::class, 'backups'])->name('admin.backups');
        Route::get('/admin/backups/{backup}/download', [AdminPageController::class, 'backupDownload'])->name('admin.backups.download');
        Route::get('/admin/alerts', [AdminPageController::class, 'alerts'])->name('admin.alerts');
    });
});
