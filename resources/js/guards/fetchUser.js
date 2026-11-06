import { useUser } from '@/stores/users';
import { useAuth } from '@/stores/auth';

export default async (to) => {
    const { read } = useUser();
    const { user } = useAuth();

    try {
        await read(to.params.id || user.id);
    } catch (e) {
        return false;
    }
}
