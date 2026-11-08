<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;

Route::middleware('throttle:sensitive')->group(function () {
    Route::post('password/forgot', [PasswordController::class, 'forgot'])->name('auth.password.forgot');
    Route::post('password/reset/{token}', [PasswordController::class, 'reset'])->name('auth.password.reset');
    Route::put('password/update/{user}', [PasswordController::class, 'update'])->name('auth.password.update');
    Route::post('register', [RegisterController::class, 'register'])->name('auth.register');
});

Route::get('auth/me', [LoginController::class, 'me'])->name('auth.me');
Route::post('logout', [LoginController::class, 'logout'])->name('auth.logout');

Route::post('login', [LoginController::class, 'login'])->name('auth.login')->middleware('throttle:login');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::post('users/{user}/impersonate', [LoginController::class, 'impersonate'])->name('impersonate');
});
