<?php

use App\Models\Leave;
use App\Models\Staff;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\RegisterController;

Route::get('/', [StaffController::class, 'show']);

// Register
Route::get('/register', [RegisterController::class, 'create']);
Route::get('/register/employee', [RegisterController::class, 'createEmployee']);
Route::get('/register/admin', [RegisterController::class, 'createAdmin']);
Route::post('/register/store/{role}', [RegisterController::class, 'store'])->name('register.store');

// Email verification
Route::get('/email/verify', 
    [AuthController::class, 'verify'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', 
    [AuthController::class, 'verufyHandler'])->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/verification-notification', 
    [AuthController::class, 'resend'])->middleware(['auth', 'throttle:6,1'])->name('verification.resend');

// Login
Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/authenticate', [LoginController::class, 'authenticate']);
Route::post('/logout', [LoginController::class, 'logout']);

// Password reset
Route::get('/forgot-password', 
    [AuthController::class, 'forgot'])->middleware('guest')->name('password.request');
Route::post('/forgot-password', 
    [AuthController::class, 'forgotHandler'])->middleware('guest')->name('password.email');
Route::get('/reset-password/{token}', 
    [AuthController::class, 'reset'])->middleware('guest')->name('password.reset');
Route::post('/reset-password', 
    [AuthController::class, 'resetHandler'])->middleware('guest')->name('password.update');


// Staff
Route::get('/profile', [StaffController::class, 'profile'])->middleware(['auth', 'verified']);

