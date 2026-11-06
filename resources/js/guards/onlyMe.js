import { useAuth } from '@/stores/auth';
import { useUser } from '@/stores/users';

export default async () => {
    const { isLogged, user: me } = useAuth();
    const { user, read } = useUser();

    if (! isLogged) {
        return { name: 'welcome' };
    }

    if (user.id === me.id) {
        return;
    }

    try {
        await read(me.id);
    } catch(e) {
        return { name: 'welcome' };
    }
}
