import Translator from '@/objects/Translator';
import Markup from '@/objects/Markup';

export default [
    {
        title: () =>  Translator.ta('name'),
        key: 'name',
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
