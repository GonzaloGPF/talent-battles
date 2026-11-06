import { useAuth } from '@/stores/auth';
import { computed, ref, watch } from 'vue';
import useHttp from '@/composables/useHttp';
import { storeToRefs } from 'pinia';

export default (url, baseParams, filterForm, avoidRequest) => {
    url = ref(url);

    filterForm = ref(filterForm);
    baseParams = ref(baseParams);

    const { user } = storeToRefs(useAuth());
    const { getJson, loading } = useHttp();

    let lastStringParams = null;
    const items = ref([]);
    const currentPage = ref(filterForm.value?.page || 1);
    const perPage = ref(15);
    const totalPages = ref(0);
    const total = ref(0);
    const allParams = computed(() => {
        const baseParamsValue = baseParams.value || {};
        const filterFormValue = filterForm.value || {};
        return {
            ...filterFormValue,
            ...baseParamsValue,
        }
    });

    watch(user, makeRequest);

    watch(allParams, makeRequest, { immediate: true, deep: true });

    function makeRequest(force) {
        if (avoidRequest) return;

        const stringParams = JSON.stringify(allParams.value);

        if (!force && lastStringParams === stringParams) {
            return Promise.resolve(items.value);
        }

        lastStringParams = stringParams;

        return getJson(url.value, { params: allParams.value })
            .then(onSuccess);
    }

    function onSuccess(response) {
        items.value = response.data;
        setPagination(response.meta);
    }

    function setPagination(meta) {
        if (! meta) return;

        currentPage.value = meta.current_page;
        total.value = meta.total;
        totalPages.value = Math.ceil(meta.total / perPage.value);
    }

    return {
        items,
        currentPage,
        totalPages,
        total,
        loading,
        makeRequest,
    }
}
