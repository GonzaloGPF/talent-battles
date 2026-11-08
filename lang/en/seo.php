<?php

return [
    'title' => ':page | :app',
    'home_title' => ':app | The social network for artists',
    'description' => 'Upload your beats, songs, samples and designs, face other artists in battles voted by the community and sell what you create.',
    'unknown_author' => 'an artist',
    'pages' => [
        'beats' => [
            'title' => 'Beats',
            'description' => 'Listen to beats from independent producers and find the one for your next track.',
        ],
        'songs' => [
            'title' => 'Songs',
            'description' => 'Listen to the songs shared by the artists of the community.',
        ],
        'samples' => [
            'title' => 'Samples',
            'description' => 'Find samples for your productions, shared by other producers.',
        ],
        'designs' => [
            'title' => 'Designs',
            'description' => 'Discover the designs shared by the artists of the community.',
        ],
        'battles' => [
            'title' => 'Battles',
            'description' => 'Two creations face to face: listen, compare and vote for the winner.',
        ],
        'users' => [
            'title' => 'Artists',
            'description' => 'Meet the producers, musicians and designers of the community.',
        ],
        'contributors' => [
            'title' => 'Contributors',
            'description' => 'The people who have supported this project financially.',
        ],
    ],
    'track_types' => [
        'beat' => 'beat',
        'song' => 'song',
        'sample' => 'sample',
    ],
    'track_title' => ':name, :type by :author',
    'track_description' => 'Listen to “:name”, :type by :author on :app.',
    'design_title' => ':name, design by :author',
    'design_description' => 'See “:name”, design by :author on :app.',
    'battle_title' => ':first vs :second',
    'battle_description' => '“:first” by :first_author against “:second” by :second_author. Listen to both and vote for the best on :app.',
    'battle_won_description' => '“:first” by :first_author beat “:second” by :second_author on :app.',
    'battle_draw_description' => '“:first” by :first_author and “:second” by :second_author drew on :app.',
    'open_battle_title' => ':name is looking for a rival',
    'open_battle_description' => '“:name” by :author is looking for a rival on :app.',
    'album_title' => ':name, album by :author',
    'album_description' => 'Listen to “:name”, album by :author on :app.',
    'user_description' => 'Listen to and discover the creations of :name on :app.',
];
