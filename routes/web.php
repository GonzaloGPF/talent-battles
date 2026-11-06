<?php

use App\Http\Controllers\BillingPortalController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InvitationClaimController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\Stripe\OrderSuccessController;
use App\Http\Controllers\UserVerifyController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [WelcomeController::class, 'index'])
    ->name('welcome');

Route::get('users/{user:token}/verify', [UserVerifyController::class, 'update'])
    ->name('users.verify');

/**
 * OAuth integrations
 */
Route::get('/{driver}/redirect', [OAuthController::class, 'redirect']);
Route::get('/{driver}/callback', [OAuthController::class, 'callback']);

Route::get('orders/{order}/success', OrderSuccessController::class)
    ->name('orders.success')
    ->middleware('signed');

Route::get('invitations/{invitation}/claim', InvitationClaimController::class)
    ->name('invitations.claim')
    ->middleware('signed');

Route::get('files/{file}', [FileController::class, 'show'])
    ->name('web.files.show');

Route::get('emails/{email}/render', [EmailController::class, 'render'])
    ->name('emails.render');

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('billing-portal', BillingPortalController::class)
        ->name('billing-portal.show');
});

// Catch-all route. It has to be a fallback: LaRecipe registers its routes after this file is loaded.
Route::fallback([WelcomeController::class, 'index']);
