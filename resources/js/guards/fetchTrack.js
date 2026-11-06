import { useTrack } from '@/stores/tracks';

export default async (to) => {
    const { read } = useTrack();

    try {
        await read(to.params.id);
    } catch (e) {
        return false;
    }
}
