import { useAuth } from '@/stores/auth';

export default async (from, to) => {
    const { isLogged } = useAuth();
    const { fetchMe } = useAuth();

    if (! isLogged) return true;

    try {
        await fetchMe();
    } catch (e) {
        if (to.name !== 'welcome') {
            return { name: 'welcome' };
        }
    }
}
