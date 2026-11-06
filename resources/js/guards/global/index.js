// import version from '@/guards/global/version';
import constants from '@/guards/global/constants';
import company from '@/guards/global/company';
import title from '@/guards/global/title';
import { useLoader } from '@/stores/loader';
import cookies from '@/guards/global/cookies';
import identity from '@/guards/global/identity';

export default async (to, from) => {
    if (to.name === from.name) {
        return true;
    }

    useLoader().setLoading(true);

    return await Promise.all([
        cookies(),
        // version(to, from),
        constants(to, from),
        company(to, from),
        identity(),
        title(to),
    ]).then(() => {
        useLoader().setLoading(false);
        return true;
    });
}
