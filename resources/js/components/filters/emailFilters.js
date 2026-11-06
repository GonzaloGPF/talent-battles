import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const { hasRole } = useAuth();

export default [
    {
        name: 'to',
    },
    {
        name: 'email_types',
        label: () => Translator.ta('email_type'),
        input: 'select',
        src: 'email_types',
        multiple: true,
    },
    {
        name: 'created_by',
        input: 'auto',
        src: 'users',
        multiple: true,
        visible: () => hasRole('admin'),
    },
    {
        name: 'sent',
        input: 'boolean',
    }
]
