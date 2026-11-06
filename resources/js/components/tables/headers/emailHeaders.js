import Markup from '@/objects/Markup';
import Formatter from '@/objects/Formatter';
import Translator from '@/objects/Translator';

export default [
    {
        title: () =>  Translator.ta('created_by'),
        key: 'createdBy.name',
    },
    {
        title: () =>  Translator.ta('email_type'),
        key: 'emailType.name',
        formatter: Markup.emailType,
    },
    {
        title: () =>  Translator.ta('to'),
        key: 'to',
        formatter: Formatter.items
    },
    {
        title: () =>  Translator.ta('sent'),
        key: 'sent',
        markup: Markup.datetime
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
]
