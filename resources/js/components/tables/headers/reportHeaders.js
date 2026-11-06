import Markup from '@/objects/Markup';
import Formatter from '@/objects/Formatter';
import Translator from '@/objects/Translator';

export default [
    {
        title: () =>  Translator.ta('report_state'),
        key: 'report_state',
        formatter: Markup.reportState
    },
    {
        title: () =>  Translator.ta('message'),
        key: 'message',
        formatter: Formatter.text
    },
    {
        title: () =>  Translator.ta('resolution'),
        key: 'resolution',
        formatter: Formatter.text
    },
    {
        title: () =>  Translator.ta('closed_at'),
        key: 'closed_at',
        markup: Markup.datetime
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
