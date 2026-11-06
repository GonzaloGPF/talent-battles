import Markup from '@/objects/Markup';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

export default [
    {
        title: () =>  Translator.ta('payment_state'),
        key: 'payment_state',
        formatter: Markup.paymentState
    },
    {
        title: () =>  Translator.modelTitle('orderLine', true),
        key: 'order_lines_count',
    },
    {
        title: () =>  Translator.ta('total'),
        key: 'total',
        formatter: Formatter.money
    },
    {
        title: () =>  Translator.ta('succeeded'),
        key: 'succeeded',
        formatter: Formatter.date
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
