import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';

export default [
    {
        name: 'name',
    },
    {
        name: 'type',
        multiple: true,
        input: 'select',
        options: Utils.options([
            'user',
            'track',
            'design',
            'album',
            // 'battle',
        ]),
    },
    {
        name: 'favorite_list',
        label: () => Translator.ta('favorite_list_id'),
        input: 'auto',
        src: 'favorite_lists',
        multiple: true,
    },
]
