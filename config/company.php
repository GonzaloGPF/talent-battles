<?php

use App\Enums\Levels;

return [
    'name' => env('APP_NAME', 'Laravel'),
    'commercial_name' => env('APP_NAME', 'Laravel'),
    'acronym' => 'TB',
    'legal' => [
        'conditions' => '2022-04-01',
        'privacy' => '2022-04-01',
        'cookies' => '2022-04-01',
    ],
    'contact' => [
        'cif' => '',
        'address' => 'Sevilla, España',
    ],

    /*
     * Percent of money Talent Battles keep when selling
     */
    'fees' => [
        'creation' => 10, // 10%
    ],

    'battle_ttl' => 5, // days

    /*
     * Battles that Talent Battles keeps in progress, pairing creations whose owners accept challenges
     */
    'battles_in_progress' => 10,

    'currency' => 'eur',

    'progress' => [
        Levels::NOVICE->value => 0,
        Levels::APPRENTICE->value => 500,
        Levels::MEDIUM->value => 1000,
        Levels::ADVANCED->value => 5000,
        Levels::EXPERT->value => 10000,
    ],

    'services' => [
        'promo' => [
            'price' => 5, // amount of money when promoting a creation
            'discount' => 5, // percent of discount for each month
            'max_discount' => 20, // max percent of discount when promoting
            'max_months_discount' => 6, // max months allowed applying discount
            'min_months_discount' => 2, // min months needed for applying discounts
        ],
        'storage' => [ // Amount of Creation you can upload
            //            PLAN::FREE => 10,
            //            PLAN::INITIAL => 50,
            //            PLAN::MEDIUM => 100,
        ],
    ],

    'buy' => [
        'points' => [
            'price' => 0.1,
            'discounts' => [ // points => discount
                50 => 0,
                250 => 20,
                500 => 25,
                1000 => 30,
            ],
        ],
    ],

    'points' => [
        'prices' => [
            'create_battle' => 20,
        ],
        'awards' => [
            'do_vote' => 1,
            'win_battle' => 30,
            'draw_battle' => 15,
            'upload_creation' => 2,
            'do_winner_vote' => 2,
        ],
    ],

    'achievements' => [
        'victories' => [
            'first' => 20,
            '10' => 20,
            '50' => 20,
            '100' => 20,
        ],
        'comments' => [
            'first' => 5,
            '10' => 5,
            '50' => 5,
            '100' => 5,
        ],
        'battles' => [
            'first' => 5,
            '10' => 5,
            '50' => 5,
            '100' => 5,
        ],
        'experience' => [
            'apprentice' => 50,
            'medium' => 100,
            'advanced' => 150,
            'expert' => 200,
        ],
        'fans' => [ // Following you
            'novice' => 10,
            'influencer' => 100,
            'promise' => 500,
            'famous' => 1000,
            'superstar' => 5000,
            'legend' => 10000,
        ],
    ],
];
