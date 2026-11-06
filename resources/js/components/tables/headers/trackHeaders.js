import Markup from '@/objects/Markup';
import Formatter from '@/objects/Formatter';
import Translator from '@/objects/Translator';

export default [
    {
        title: () =>  Translator.ta('track_type'),
        key: 'track_type',
        formatter: value => Translator.tConstName('trackType', value)
    },
    {
        title: () =>  Translator.ta('name'),
        key: 'name',
    },
    {
        title: () =>  Translator.tl('is_promoted'),
        key: 'is_promoted',
        formatter: Formatter.boolean,
        sortable: false,
    },
    {
        title: () =>  Translator.modelTitle('like', true),
        key: 'likes_count',
        sortable: false,
    },
    {
        title: () =>  Translator.modelTitle('favorite', true),
        key: 'favorites_count',
        sortable: false,
    },
    {
        title: () =>  Translator.modelTitle('comment', true),
        key: 'comments_count',
        sortable: false,
    },
    {
        title: () =>  Translator.modelTitle('download', true),
        key: 'downloads_count',
        sortable: false,
    },
    {
        title: () =>  Translator.ta('price'),
        key: 'product.price',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('last_sale'),
        key: 'product.last_sale',
        markup: Markup.datetime
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
