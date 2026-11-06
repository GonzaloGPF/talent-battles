import Translator from '@/objects/Translator';

export default [
    {
        name: 'email',
    },
    {
        name: 'name',
    },
    {
        name: 'accepted',
        title: Translator.ta('accepted'),
        input: 'boolean',
    },
    {
        name: 'has_account',
        title: Translator.ta('has_account'),
        input: 'boolean',
    },
]
