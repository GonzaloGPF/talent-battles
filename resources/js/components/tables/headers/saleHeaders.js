import Markup from '@/objects/Markup';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

export default [
    {
        title: () =>  Translator.ta('description'),
        key: 'description',
    },
    {
        title: () =>  Translator.ta('amount'),
        key: 'amount',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('commission'),
        key: 'commission',
        formatter: Formatter.percent
    },
    {
        title: () =>  Translator.ta('commission_amount'),
        key: 'commission_amount',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('total'),
        key: 'total',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
