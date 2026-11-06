import Translator from '@/objects/Translator';

export default [
    {
        name: 'name',
    },
    {
        name: 'tags',
        label: () => Translator.tl('tags'),
        input: 'combo',
        src: 'tags',
        multiple: true,
    },
    {
        name: 'promoted',
        input: 'check',
    },
    {
        name: 'exclusive',
        input: 'boolean',
    },
    {
        name: 'in_sale',
        label: () => Translator.tl('in_sale'),
        input: 'boolean',
    },
    {
        name: 'sold',
        label: () => Translator.tl('sold'),
        input: 'boolean',
    },
]
