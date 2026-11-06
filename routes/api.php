<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\BattleController;
use App\Http\Controllers\BattleVoteController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConstantsController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DesignController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EmailController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FavoriteListController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderCompleteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PayNowController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

require __DIR__.'/auth.php';

Route::put('locales', [LocaleController::class, 'update'])->name('locales.update');
Route::get('constants', [ConstantsController::class, 'index'])->name('constants.index');
Route::get('company', [CompanyController::class, 'index'])->name('company.index');

Route::apiResource('users', UserController::class);
Route::apiResource('tracks', TrackController::class);
Route::apiResource('designs', DesignController::class);
Route::apiResource('albums', AlbumController::class);
Route::apiResource('battles', BattleController::class)->only(['show', 'index', 'destroy', 'store']);
Route::apiResource('battles.votes', BattleVoteController::class)->only('store');
Route::apiResource('countries', CountryController::class)->only(['index']);
Route::apiResource('regions', RegionController::class)->only(['index']);
Route::apiResource('activities', ActivityController::class)->only(['index']);
Route::apiResource('tags', TagController::class)->only(['index']);
Route::apiResource('donations', DonationController::class)->only('index');
Route::apiResource('reports', ReportController::class)->only(['show', 'index', 'store', 'update']);
Route::put('reports/{report}/solve', [ReportController::class, 'solve'])->name('reports.solve');
Route::apiResource('emails', EmailController::class)->only(['show', 'index']);
Route::post('emails/contact', [EmailController::class, 'contact'])->name('emails.contact');
Route::put('emails/{email}/resend', [EmailController::class, 'resend'])->name('emails.resend');
Route::put('notifications/read/{notification?}', [NotificationController::class, 'read'])->name('notifications.read');
Route::apiResource('notifications', NotificationController::class)->only('index');
Route::apiResource('favorite_lists', FavoriteListController::class)->except('show');
Route::apiSingleton('carts', CartController::class)->only('show', 'update');
Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'store']);
Route::apiResource('sales', SaleController::class)->only(['index']);
Route::apiResource('products', ProductController::class);
Route::apiResource('invitations', InvitationController::class)->except(['destroy', 'show']);
Route::get('orders/{order}/complete', [OrderCompleteController::class, 'show'])->name('orders.complete');
Route::post('pay_now', [PayNowController::class, 'store'])->name('pay_now.store');

/**
 * Polymorphic Resources
 */
Route::get('files/multiple', [FileController::class, 'zip'])->name('files.zip');
// Route::get('files/creation', [FileController::class, 'creation'])->name('files.creation');
Route::resource('files', FileController::class)->except(['edit', 'create']);
Route::resource('comments', CommentController::class)->only(['index', 'store', 'destroy']);
Route::resource('likes', LikeController::class)->only(['index', 'store', 'destroy']);
Route::ApiResource('favorites', FavoriteController::class);
Route::resource('downloads', DownloadController::class)->only(['index', 'store']);

require __DIR__.'/stripe.php';
require __DIR__.'/stats.php';
