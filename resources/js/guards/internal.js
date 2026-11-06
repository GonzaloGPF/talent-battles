import { useAuth } from '@/stores/auth';

export default async (to, from) => {
    const { hasGroup } = useAuth();

    if (! hasGroup('internals')) {
        return (from);
    }

    return true;
}
