import { defineStore } from 'pinia';
import AlbumHelper from '@/helpers/AlbumHelper';
import Utils from '@/objects/Utils';
import { ref } from 'vue';
import useHttp from '@/composables/useHttp';

export const useAlbum = defineStore('albums', () => {
    const { getJson, postJson, deleteJson } = useHttp();

    const album = ref({});

    const fetch = (params) => getJson('albums', { params });

    const create = (form) => postJson('albums', form);

    const read = async (id) => {
        let params = AlbumHelper.showParams;

        const { data } = await getJson(`albums/${ id }`, { params });

        album.value = data;
    }
    const update = (form) => {
        form._method = 'PUT';
        return postJson(`albums/${ form.id }`, Utils.toFormData(form));
    }
    const destroy = (id) => deleteJson(`albums/${ id }`);

    return {
        album,
        fetch,
        create,
        read,
        update,
        destroy,
    }
});
