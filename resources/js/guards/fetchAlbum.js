import { useAlbum } from '@/stores/albums';

export default async (to) => {
    const { read } = useAlbum()

    try {
        await read(to.params.id);
    } catch (e) {
        return false;
    }
}
