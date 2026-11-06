import global from '@/config/global'
import { useVersion } from '@/stores/version';
import { useConstant } from '@/stores/constants';

export default async () => {
    const { fetch, version } = useVersion();
    const { read } = useConstant();

    if (global.isLocal()) return true;

    try {
        const latestVersion = await fetch();

        if (version !== latestVersion) {
            await read();

            window.location.reload(true);
        }
    } catch (e) {
        console.error(e);
    }
}
