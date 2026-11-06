import Translator from '@/objects/Translator';
import router from '@/plugins/router';

export default [
    {
        name: 'name'
    },
    {
        name: 'thematics',
        label: () => Translator.ta('thematic'),
        input: 'select',
        src: 'thematics',
        multiple: true,
    },
    {
        name: 'design_types',
        label: () => Translator.ta('design_type'),
        input: 'select',
        src: 'design_types',
        multiple: true,
        // visible: () => router.name === 'admin.designs.index',
    },
    {
        name: 'styles',
        label: () => Translator.ta('style'),
        input: 'select',
        src: 'styles',
        multiple: true,
    },
    {
        name: 'aspect_ratios',
        label: () => Translator.ta('aspect_ratio'),
        input: 'select',
        src: 'aspect_ratios',
        multiple: true,
    },
    {
        name: 'created_by',
        input: 'auto',
        src: 'users',
        urlAttribute: 'nick_name',
        multiple: true,
        visible: () => router.currentRoute.value?.name !== 'admin.designs.index',
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
        label: () => Translator.ta('album_id'),
        input: 'auto',
        src: 'albums',
        multiple: true,
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
