import Markup from '@/objects/Markup';
import Translator from '@/objects/Translator';

export default [
    {
        title: () =>  Translator.ta('battle_state'),
        key: 'battle_state',
        formatter: Markup.battleState
    },
    {
        title: () =>  Translator.ta('battle_type'),
        key: 'battle_type',
        formatter: value => Translator.tConstName('battle_types', value)
    },
    {
        title: () =>  Translator.tl('winner'),
        key: 'winner.name',
    },
    {
        title: () =>  Translator.tl('votes'),
        key: 'votes_count',
    },
    {
        title: () =>  Translator.tl('points'),
        key: 'votes',
        formatter: votes => (votes || [])
            .map(vote => vote.value)
            .reduce((value, sum) => value + sum, 0)
    },
    {
        title: () =>  Translator.ta('created_at'),
        key: 'created_at',
        markup: Markup.datetime
    },
    {
        title: () =>  Translator.tl('ending'),
        key: 'ending',
        markup: Markup.datetime
    },
]
