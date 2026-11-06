import Translator from '@/objects/Translator';
import router from '../../plugins/router';

export default [
    {
        name: 'name',
    },
    {
        name: 'genres',
        label: () => Translator.ta('genre'),
        input: 'select',
        src: 'genres',
        multiple: true,
    },
    {
        name: 'thematics',
        label: () => Translator.ta('thematic'),
        input: 'select',
        src: 'thematics',
        multiple: true,
    },
    {
        name: 'keys',
        label: () => Translator.ta('key'),
        input: 'select',
        src: 'keys',
        multiple: true,
    },
    {
        name: 'instruments',
        label: () => Translator.ta('instrument'),
        input: 'select',
        src: 'instruments',
        multiple: true,
    },
    {
        name: 'track_types',
        label: () => Translator.ta('track_type'),
        input: 'select',
        src: 'track_types',
        multiple: true,
        visible: () => router.currentRoute.value?.name === 'admin.tracks.index',
    },
    {
        name: 'created_by',
        input: 'auto',
        src: 'users',
        urlAttribute: 'nick_name',
        multiple: true,
        visible: () => router.currentRoute.value?.name !== 'admin.tracks.index',
    },
    {
        name: 'tags',
        label: () => Translator.tl('tags'),
        input: 'combo',
        src: 'tags',
        multiple: true,
    },
    {
        name: 'album',
        label: () => Translator.modelTitle('album'),
        input: 'auto',
        src: 'albums',
        multiple: true,
    },
    {
        name: 'bpm',
        label: () => Translator.ta('bpm'),
        input: 'number_range',
        range: true,
        min: 1,
        max: 200,
    },
    {
        name: 'duration',
        label: () => `${ Translator.ta('duration') } (${ Translator.tl('time.seconds') })`,
        input: 'number_range',
        range: true,
        min: 1,
        max: 600,
    },
    {
        name: 'promoted',
        input: 'check',
    },
    {
        name: 'exclusive',
        input: 'boolean',
    },
    {
        name: 'in_sale',
        label: () => Translator.tl('in_sale'),
        input: 'boolean',
    },
    {
        name: 'sold',
        label: () => Translator.tl('sold'),
        input: 'boolean',
    },
]
