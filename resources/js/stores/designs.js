import { defineStore } from 'pinia';
import useHttp from '@/composables/useHttp';
import { ref } from 'vue';
import DesignHelper from '@/helpers/DesignHelper';

export const useDesign = defineStore('designs', () => {
    const { getJson, postJson, deleteJson } = useHttp();

    const design = ref({});
    const fetch = (params) => getJson('designs', { params })
    const create = (form) => postJson('designs', form);
    const read = async (id) => {
        let params = DesignHelper.showParams;

        const { data } = await getJson(`designs/${ id }`, { params });

        return design.value = data;
    }
    const update = (form) => {
        form._method = 'PUT';
        return postJson(`designs/${ form.id }`, form);
    }
    const destroy = (id) => deleteJson(`designs/${ id }`);

    return {
        design,
        fetch,
        create,
        read,
        update,
        destroy,
    }
})
