import Markup from '@/objects/Markup';
import Translator from '@/objects/Translator';

export default [
    {
        title: () =>  Translator.tl('type'),
        key: 'type',
        formatter: value => Translator.modelTitle(value)
    },
    {
        title: () =>  Translator.ta('favorite_list_id'),
        key: 'favoriteList.name',
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
