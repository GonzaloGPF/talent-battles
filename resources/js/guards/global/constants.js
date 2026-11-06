import { useConstant } from '@/stores/constants';

export default async () => {
    if (! useConstant().loaded) {
        try {
            await useConstant().read();
        } catch (e) {
            return false;
        }
    }
}

