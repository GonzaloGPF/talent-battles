import Translator from '@/objects/Translator';

export default [
    {
        name: 'name'
    },
    {
        name: 'product_types',
        label: () => Translator.ta('product_type'),
        input: 'select',
        src: 'product_types',
        multiple: true,
    },
    {
        name: 'price',
        label: () => Translator.ta('price'),
        input: 'number_range',
        range: true,
        min: 1,
        max: 200,
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
