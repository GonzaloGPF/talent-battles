export default {
    showRoute(design) {
        if (! design) return;

        return {
            name: 'designs.show',
            params: { id: design.id }
        };
    },
    inputNames: [
        'id',
        'design_type',
        'tags',
        'name',
        'color',
        'album_id',
        'can_challenge',
        'aspect_ratio',
        'description',
        'battle',
        'image',
        'sell',
        'auto_tag',
        'price',
        'exclusive',
        'file_to_sell',
        'last_sale',
    ],
    indexParams: {
        with: [
            'album.image',
            'image',
            'createdBy.image',
            'like',
            'favorite',
            'comment',
            'download',
            'product',
            'currentBattle',
            'orderLine',
        ],
        counts: [
            'likes',
            'favorites',
            'downloads',
            'comments',
        ],
    },
    showParams: {
        with: [
            'album.image',
            'image',
            'createdBy.image',
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
