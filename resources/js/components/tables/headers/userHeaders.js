import Markup from '@/objects/Markup';
import Translator from '@/objects/Translator';

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
        title: () =>  Translator.ta('role'),
        key: 'role',
        formatter: Markup.role
    },
    {
        title: () =>  Translator.ta('points'),
        key: 'points',
    },
    {
        title: () =>  Translator.ta('experience'),
        key: 'experience',
    },
    {
        title: () =>  Translator.ta('locale'),
        key: 'locale',
    },
    {
        title: () =>  Translator.ta('verified_at'),
        key: 'verified_at',
        markup: Markup.datetime
    },
]
