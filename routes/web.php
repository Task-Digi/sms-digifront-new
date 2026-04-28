<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;

// Root domain redirects to easypublishnow.com
Route::get('/', fn() => redirect()->away('https://easypublishnow.com'));

// All SMS system routes under /sms prefix
Route::prefix('sms')->group(function () {
    Route::middleware('otp')->group(function () {
        Route::get('/home', fn() => view('home'))->name('home');
        Route::get('/tracking', fn() => view('admin.tracking'))->name('get.tracking');
    });

    Route::get('/', [LoginController::class, 'index'])->name('admin.login');
    Route::get('/login', fn() => redirect()->route('admin.login'));
    Route::get('/auth/redirect', [LoginController::class, 'redirectToProvider'])->name('auth.redirect');
    Route::get('/auth/callback', [LoginController::class, 'callback'])->name('auth.callback');
    Route::get('/logout', [LoginController::class, 'logout'])->name('admin.logout');
});
