import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const { hasRole } = useAuth();

export default [
    {
        name: 'email',
        visible: () => hasRole('admin'),
    },
    {
        name: 'name',
    },
    {
        name: 'nick',
    },
    {
        name: 'roles',
        label: () => Translator.ta('role'),
        input: 'select',
        src: 'roles',
        multiple: true,
        visible: () => hasRole('admin'),
    },
    {
        name: 'gender',
        input: 'select',
        src: 'genders',
        visible: () => hasRole('admin'),
    },
    {
        name: 'skills',
        input: 'select',
        src: 'skills',
        multiple: true,
    },
    {
        name: 'categories',
        input: 'select',
        src: 'categories',
        multiple: true,
    },
    {
        name: 'verified',
        title: Translator.ta('verified_at'),
        input: 'boolean',
    }
]
