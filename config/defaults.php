<?php

use App\Events\ReportClosed;
use App\Notifications\BattleDraw;
use App\Notifications\BattleLost;
use App\Notifications\BattleStarted;
use App\Notifications\BattleWon;
use App\Notifications\CommentReceived;
use App\Notifications\DesignSold;
use App\Notifications\DownloadReceived;
use App\Notifications\FavoriteReceived;
use App\Notifications\LikeReceived;
use App\Notifications\OrderCompleted;
use App\Notifications\ReportReceived;
use App\Notifications\ReportSolved;
use App\Notifications\TrackSold;

return [
    'initial_points' => 50,
    'social_settings' => [
        'youtube' => null,
        'twitter' => null,
        'soundcloud' => null,
        'soundclick' => null,
        'facebook' => null,
        'instagram' => null,
        'web' => null,
    ],
    'notification_settings' => [
        toSnake(LikeReceived::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(FavoriteReceived::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(CommentReceived::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(DownloadReceived::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(BattleStarted::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(BattleWon::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(BattleLost::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(BattleDraw::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(OrderCompleted::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(TrackSold::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(DesignSold::class) => [
            'mail' => false,
            'broadcast' => true,
        ],
        toSnake(ReportReceived::class) => [
            'mail' => true,
            'broadcast' => true,
        ],
        toSnake(ReportSolved::class) => [
            'mail' => true,
            'broadcast' => true,
        ],
        toSnake(ReportClosed::class) => [
            'mail' => true,
            'broadcast' => true,
        ],
    ],
];
