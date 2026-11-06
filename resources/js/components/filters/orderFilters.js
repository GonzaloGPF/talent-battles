import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const { hasRole } = useAuth();

export default [
    {
        name: 'total',
        input: 'number_range',
        range: true,
        min: 0,
    },
    {
        name: 'created_by',
        input: 'auto',
        src: 'users',
        multiple: true,
        visible: () => hasRole('admin'),
    },
    {
        name: 'payment_states',
        label: () => Translator.ta('payment_state'),
        input: 'select',
        src: 'payment_states',
        multiple: true,
    },
]
