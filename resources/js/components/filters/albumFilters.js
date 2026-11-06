import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const { hasRole } = useAuth();

export default [
    {
        name: 'name'
    },
    {
        name: 'description'
    },
    {
        name: 'created_by',
        input: 'auto',
        src: 'users',
        urlAttribute: 'nick_name',
        multiple: true,
        visible: () => hasRole('admin')
    },
    {
        name: 'tags',
        label: () => Translator.tl('tags'),
        input: 'combo',
        src: 'tags',
        multiple: true,
    },
]
