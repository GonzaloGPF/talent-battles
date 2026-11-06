import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const { hasRole } = useAuth();

export default [
    {
        name: 'name'
    },
    {
        name: 'battle_states',
        label: () => Translator.ta('battle_state'),
        input: 'select',
        src: 'battle_states',
        multiple: true,
    },
    {
        name: 'battle_types',
        label: () => Translator.ta('battle_type'),
        input: 'select',
        src: 'battle_types',
        multiple: true,
    },
    {
        name: 'won',
        label: () => Translator.tl('won'),
        input: 'boolean',
    },
    {
        name: 'won_by',
        label: () => Translator.tl('won_by'),
        input: 'auto',
        src: 'users',
        multiple: true,
        visible: () => hasRole('admin')
    },
    {
        name: 'lost_by',
        label: () => Translator.tl('lost_by'),
        input: 'auto',
        src: 'users',
        multiple: true,
        visible: () => hasRole('admin')
    },
    {
        name: 'by_participants',
        label: () => Translator.tl('participants'),
        input: 'auto',
        src: 'users',
        multiple: true,
        visible: () => hasRole('admin')
    },
]
