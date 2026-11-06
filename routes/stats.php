<?php

use App\Http\Controllers\Stats\ActivityReceivedController;
use App\Http\Controllers\Stats\ActivitySummaryCardController;
use App\Http\Controllers\Stats\BattleSummaryCardController;
use App\Http\Controllers\Stats\BattleSummaryController;
use App\Http\Controllers\Stats\CreationSalesController;
use App\Http\Controllers\Stats\DesignSummaryCardController;
use App\Http\Controllers\Stats\DesignSummaryController;
use App\Http\Controllers\Stats\SalesCardController;
use App\Http\Controllers\Stats\TrackSummaryCardController;
use App\Http\Controllers\Stats\TrackSummaryController;

Route::prefix('stats')
    ->name('stats.')
    ->middleware('auth:sanctum')
    ->group(function () {
        //        Route::get('creations_summary_card', CreationSummaryCardController::class)->name('creations.summary.card');
        Route::get('tracks_summary_card', TrackSummaryCardController::class)->name('tracks.summary.card');
        Route::get('designs_summary_card', DesignSummaryCardController::class)->name('designs.summary.card');
        Route::get('battles_summary_card', BattleSummaryCardController::class)->name('battle.summary.card');
        Route::get('creation_sales_card', SalesCardController::class)->name('creation.sales.card');
        Route::get('activity_summary_card', ActivitySummaryCardController::class)->name('activity.summary.card');

        Route::get('tracks_summary', TrackSummaryController::class)->name('tracks.summary');
        Route::get('designs_summary', DesignSummaryController::class)->name('designs.summary');
        Route::get('battles_summary', BattleSummaryController::class)->name('battles.summary');
        Route::get('activity_received', ActivityReceivedController::class)->name('activities.received');
        Route::get('creation_sales', CreationSalesController::class)->name('creation.sales');
    });
