import useHttp from '@/composables/useHttp';
import Cookies from 'js-cookie';

export default async () => {
    if (! Cookies.get('XSRF-TOKEN')) {
        await useHttp().getJson('csrf-cookie', { quietly: true });
    }
}
