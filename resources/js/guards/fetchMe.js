import { useUser } from '@/stores/users';
import { useAuth } from '@/stores/auth';

export default async () => {
    const { fetchMe } = useUser();
    const { isLogged } = useAuth();

    if (isLogged) {
        return;
    }

    try {
        const { data } = await fetchMe();

        if (! data) {
            return { name: 'welcome' };
        }
    } catch (e) {
        return { name: 'welcome' };
    }
}
