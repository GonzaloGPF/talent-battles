import Markup from '@/objects/Markup';
import Formatter from '@/objects/Formatter';
import Translator from '@/objects/Translator';

export default [
    {
        title: () =>  Translator.ta('name'),
        key: 'name',
    },
    {
        title: () =>  Translator.tl('is_promoted'),
        key: 'is_promoted',
        formatter: Formatter.boolean
    },
    {
        title: () =>  Translator.ta('price'),
        key: 'price',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('last_sale'),
        key: 'last_sale',
        markup: Markup.datetime
    },
]
