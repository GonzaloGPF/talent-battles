import { defineStore } from 'pinia';
import UserHelper from '@/helpers/UserHelper';
import { ref } from 'vue';
import useHttp from '@/composables/useHttp';

export const useUser = defineStore('users', () => {
    const { getJson, putJson, postJson, deleteJson, loading } = useHttp();

    const user = ref({});

    const fetch = (params) => getJson('users', { params });

    const create = (form) => postJson('users', form);

    const read = async (id) => {
        const params = UserHelper.showParams;

        const { data } = await getJson(`users/${ id }`, { params });

        return user.value = data;
    }

    const update = (id, form) => {
        if (form instanceof FormData) {
            form.append('_method', 'PUT');

            return postJson(`users/${ id }`, form);
        }

        return putJson(`users/${ id }`, form);
    }

    const destroy = (id) => deleteJson(`users/${ id }`);

    return {
        loading,
        user,
        fetch,
        create,
        read,
        update,
        destroy,
    }
});
