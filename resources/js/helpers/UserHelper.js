export default {
    inputNames: [
        'id',
        'name',
        'last_name',
        'nick',
        'email',
        'birthdate',
        'gender',
        'phone',
        'locale',
        'currency',
        'quote',
        'bio',
        'skills',
        'categories',
        'address',
        'social_settings',
        'notification_settings',
    ],
    indexParams: {
        with: [
            'like',
            'favorite',
            'comment',
            'image',
        ],
        counts: [
            'likes',
            'favorites',
            'comments',
            'beats',
            'samples',
            'songs',
            'designs',
            'albums',
        ]
    },
    showParams: {
        with: [
            'image',
            'favorite',
            'like',
        ],
        counts: [
            'favorites',
            'likes',
            'comments',
            'samples',
            'songs',
            'beats',
            'designs',
            'albums',
        ]
    },
    actionButtons: [
        'like',
        'favorite',
        'comment',
        'share',
    ]
}
