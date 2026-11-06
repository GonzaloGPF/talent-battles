import Markup from '@/objects/Markup';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

export default [
    {
        title: () =>  Translator.ta('name'),
        key: 'name',
    },
    {
        title: () =>  Translator.ta('email'),
        key: 'email',
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
    {
        title: () =>  Translator.actionTitle('accepted', null, false),
        key: 'user_id',
        markup: Formatter.boolean
    },
    {
        title: () =>  Translator.tl('account'),
        key: 'has_account',
        markup: Formatter.boolean
    },
]
