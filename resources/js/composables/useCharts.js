import useHttp from '@/composables/useHttp';
import { ref, watch } from 'vue';

export default (url, params = {}) => {
    const { loading, getJson } = useHttp();
    const chartData = ref({});
    params = ref(params);

    const makeRequest = () => {
        return getJson(url, { ...params.value })
            .then(({ data }) => chartData.value = data);
    }

    watch(params, makeRequest, { immediate: true});

    return {
        chartData,
        loading,
    }
}
