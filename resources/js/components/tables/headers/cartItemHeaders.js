import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';
import Markup from '@/objects/Markup';

export default [
    // {
    //     title: () =>  Translator.tl('type'),
    //     key: 'type',
    //     classes: 'hidden lg:table-cell',
    // },
    {
        title: () =>  Translator.ta('description'),
        key: 'description',
    },
    {
        title: () =>  Translator.ta('price'),
        key: 'price',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('quantity'),
        key: 'quantity',
        // formatter: (value, item) => {
        //     if (item.type === 'points') {
        //         return `${value} ${Translator.tl('points')}`;
        //     }
        //
        //     if (item.type === 'promo') {
        //         return `${value} ${Translator.tl('months')}`;
        //     }
        //
        //     return value;
        // }
    },
    {
        title: () =>  Translator.ta('subtotal'),
        key: 'subtotal',
        formatter: Formatter.money,
        classes: 'hidden lg:table-cell',
    },
    {
        title: () =>  Translator.ta('discount'),
        key: 'discount',
        markup: (value, item) => Markup.amountAndPercent(item.discount_amount, item.discount),
    },
    {
        title: () =>  Translator.ta('total'),
        key: 'total',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('tax'),
        key: 'tax',
        markup: (value, item) => Markup.amountAndPercent(item.tax_amount, item.tax),
    },
    {
        title: () =>  Translator.tl('final'),
        key: 'total_with_taxes',
        formatter: Formatter.money
    },
];
