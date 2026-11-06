<?php

use App\Http\Controllers\Stripe\AccountController;
use App\Http\Controllers\Stripe\DefaultPaymentMethodController;
use App\Http\Controllers\Stripe\PaymentIntentController;
use App\Http\Controllers\Stripe\PaymentMethodController;

// There is also a POST stripe/webhook route (not api) registered by Cashier
Route::prefix('stripe')
    ->name('stripe.')
//    ->middleware('auth:sanctum')
    ->group(function () {
        Route::get('default_payment_methods', [DefaultPaymentMethodController::class, 'show'])->name('default_payment_methods.show');
        Route::put('default_payment_methods/{id}', [DefaultPaymentMethodController::class, 'update'])->name('default_payment_methods.update');
        Route::resource('payment_methods', PaymentMethodController::class)->only(['index', 'store', 'destroy']);
        Route::resource('accounts', AccountController::class)->only(['index', 'store', 'destroy']);
        Route::post('accounts/link', [AccountController::class, 'link'])->name('accounts.links.store');
        Route::resource('payment_intents', PaymentIntentController::class)->only(['store', 'update']);
    });
