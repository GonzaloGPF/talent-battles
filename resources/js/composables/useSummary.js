import useHttp from '@/composables/useHttp';
import { computed, onMounted, ref, watch } from 'vue';
import Utils from '@/objects/Utils';

export default (url, params = {}) => {
    const { loading, getJson } = useHttp();
    const dataResponse = ref(null);

    params = ref(params);

    const total = computed(() => dataResponse.value?.total || 0)

    watch(params, (newValue, oldValue) => {
        newValue = Utils.removeEmpty(newValue);
        oldValue = Utils.removeEmpty(oldValue);

        if (Utils.areEquals(newValue, oldValue)) return;

        makeRequest();
    }, { deep: true });

    onMounted(() => makeRequest());

    const makeRequest = () => {
        if (! url) throw 'No url defined!';

        getJson(url, { ...params.value })
            .then(onSuccess)
    }

    const refresh = () => makeRequest();

    const onSuccess = ({ data }) => dataResponse.value = data;

    return {
        total,
        loading,
        dataResponse,
        refresh,
    }
}
