import useHttp from '@/composables/useHttp';
import { ref, watch } from 'vue';

export default (modelId, baseUrl, params = {}) => {
    modelId = ref(modelId);
    params = ref(params);
    const { loading, getJson } = useHttp();
    const vModel = ref(null);

    const getModel = () => {
        if (! modelId.value) return;

        return getJson(`${ baseUrl }/${ modelId.value }`, { params: params.value })
            .then(({ data }) => vModel.value = data);
    }

    const refresh = () => {
        getModel();
    }

    watch(modelId, () => getModel(), { immediate: true });

    return {
        vModel,
        loading,
        refresh,
    }
}
