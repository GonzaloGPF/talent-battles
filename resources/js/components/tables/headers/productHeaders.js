import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';
import Markup from '@/objects/Markup';

export default [
    {
        title: () =>  Translator.ta('product_type'),
        key: 'product_type',
        formatter: value => Translator.tConstName('productType', value)
    },
    {
        title: () =>  Translator.ta('name'),
        key: 'name',
    },
    {
        title: () =>  Translator.ta('price'),
        key: 'price',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('exclusive'),
        key: 'exclusive',
        formatter: Formatter.boolean
    },
    {
        title: () =>  Translator.ta('sales'),
        key: 'sales',
    },
    {
        title: () =>  Translator.ta('last_sale'),
        key: 'last_sale',
        formatter: Markup.datetime
    },
    {
        title: () =>  Translator.ta('promoted'),
        key: 'promoted',
        formatter: Formatter.date
    },
];
