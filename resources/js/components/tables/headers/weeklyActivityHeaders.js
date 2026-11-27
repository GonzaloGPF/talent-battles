import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

export default [
    {
        title: () => Translator.tl('week'),
        key: 'week',
        formatter: Formatter.date,
    },
    {
        title: () => Translator.tl('registrations'),
        key: 'registrations',
    },
    {
        title: () => Translator.tl('votes'),
        key: 'votes',
    },
    {
        title: () => Translator.tl('user_battles'),
        key: 'user_battles',
    },
    {
        title: () => Translator.tl('paired_battles'),
        key: 'paired_battles',
    },
    {
        title: () => Translator.tl('creations'),
        key: 'creations',
    },
];
