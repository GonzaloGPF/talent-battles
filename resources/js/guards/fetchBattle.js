import { useBattle } from '@/stores/battles';

export default async (to) => {
    const { read } = useBattle();

    try {
        await read(to.params.id);
    } catch (e) {
        return false;
    }
}
