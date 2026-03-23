<?php

use Illuminate\Support\Facades\Route;

Route::middleware('otp')->group(function () {
    Route::get('/home', fn() => view('home'))->name('home');
    Route::get('/tracking', fn() => view('admin.tracking'))->name('get.tracking');
});

Route::get('/', function () {
    if (session('login_status') === true) {
        return redirect()->route('home');
    }
    if (session()->has('mobile')) {
        return view('admin.otp');
    }
    return view('admin.login');
})->name('admin.login');

Route::get('/login', fn() => redirect()->route('admin.login'));

Route::get('/back-to-login', function () {
    session()->forget('mobile');
    return redirect()->route('admin.login');
})->name('admin.login.back');

Route::get('/logout', function () {
    session()->flush();
    return redirect()->route('admin.login');
})->name('admin.logout');
