<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LoginPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class);
Route::get('/login', LoginPageController::class)->name('login');
Route::get('/dashboard', DashboardController::class)->name('dashboard');
