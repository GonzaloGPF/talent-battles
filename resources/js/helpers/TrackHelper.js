import pluralize from 'pluralize';

export default {
    showRoute(track) {
        if (! track || ! track.track_type) return;

        const plural = pluralize.plural(track.track_type);

        return {
            name: `${ plural }.show`,
            params: { id: track.id }
        };
    },

    inputNames: [
        'id',
        'track_type',
        'tags',
        'name',
        'color',
        'album_id',
        'can_challenge',
        'bpm',
        'genres',
        'key',
        'instruments',
        'description',
        'battle',
        'audio',
        'image',
        'sell',
        'auto_tag',
        'price',
        'exclusive',
        'file_to_sell',
        'last_sale',
    ],

    showParams: {
        with: [
            'createdBy.image',
            'album.image',
            'image',
            'audio',
            'like',
            'favorite',
            'comment',
            'download',
            'product',
            'currentBattle',
            'openBattle',
            'battles',
            'orderLine',
            'product.file',
        ],
        counts: [
            'likes',
            'favorites',
            'downloads',
            'comments',
        ],
        waveform: true
    },
    indexParams: {
        with: [
            'album.image',
            'image',
            'audio',
            'createdBy.image',
            'like',
            'favorite',
            'comment',
            'download',
            'product',
            'currentBattle',
            'orderLine'
        ],
        counts: [
            'likes',
            'favorites',
            'downloads',
            'comments',
        ],
        waveform: true
    },
    actionButtons: [
        'info',
        'buy',
        'battle',
        'like',
        'favorite',
        'comment',
        'download',
        'share',
    ],
}
