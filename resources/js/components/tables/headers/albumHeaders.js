import Translator from '@/objects/Translator';
import Markup from '@/objects/Markup';

export default [
    {
        title: () =>  Translator.ta('name'),
        key: 'name',
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
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
