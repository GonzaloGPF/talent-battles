<?php

use App\Enums\TrackTypes;
use App\Http\Controllers\BillingPortalController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InvitationClaimController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Stripe\OrderSuccessController;
use App\Http\Controllers\UserVerifyController;
use App\Http\Controllers\WelcomeController;
use App\Responses\PageResponse;
use Illuminate\Http\Response;
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
Route::get('/{driver}/redirect', [OAuthController::class, 'redirect'])
    ->whereIn('driver', OAuthController::DRIVERS);
Route::get('/{driver}/callback', [OAuthController::class, 'callback'])
    ->whereIn('driver', OAuthController::DRIVERS);

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

Route::get('sitemap.xml', SitemapController::class)
    ->name('sitemap');

/**
 * Pages of the SPA with their own meta tags. A missing model still gets the SPA, with a 404.
 */
Route::controller(PageController::class)
    ->name('pages.')
    ->missing(fn () => app(PageResponse::class)->render(status: Response::HTTP_NOT_FOUND))
    ->group(function () {
        Route::get('{section}', 'index')
            ->whereIn('section', PageController::SECTIONS)
            ->name('index');

        Route::get('{trackType}/{track}', 'track')
            ->whereIn('trackType', array_map(fn (TrackTypes $type) => $type->getUrlSegment(), TrackTypes::cases()))
            ->whereNumber('track')
            ->name('tracks.show');

        Route::get('designs/{design}', 'design')
            ->whereNumber('design')
            ->name('designs.show');

        Route::get('battles/{battle}', 'battle')
            ->whereNumber('battle')
            ->name('battles.show');

        Route::get('albums/{album}', 'album')
            ->whereNumber('album')
            ->name('albums.show');

        Route::get('users/{user}', 'user')
            ->whereNumber('user')
            ->name('users.show');

        Route::get('{section}/{path?}', 'private')
            ->whereIn('section', PageController::PRIVATE_SECTIONS)
            ->where('path', '.*')
            ->name('private');
    });

// Catch-all route. It has to be a fallback: LaRecipe registers its routes after this file is loaded.
Route::fallback([WelcomeController::class, 'notFound']);
