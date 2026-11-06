import { useDesign } from '@/stores/designs';

export default async (to) => {
    const { read } = useDesign();

    try {
        await read(to.params.id);
    } catch (e) {
        return false;
    }
}
