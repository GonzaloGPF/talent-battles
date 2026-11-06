import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const { hasRole } = useAuth();

export default [
    {
        name: 'message',
    },
    {
        name: 'resolution',
    },
    {
        name: 'report_states',
        label: () => Translator.ta('report_state'),
        input: 'select',
        src: 'report_states',
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
        name: 'solved',
        label: () => Translator.tl('solved'),
        input: 'boolean',
    },
    {
        name: 'has_creation',
        label: () => Translator.tl('has_creation'),
        input: 'boolean',
    },
    {
        name: 'closed_at',
        input: 'date',
        range: true,
    },
]
