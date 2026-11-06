import { useAuth } from '@/stores/auth';

export default async () => {
    const { isLogged } = useAuth();

    if (! isLogged) {
        return { name: 'welcome' };
    }
}
