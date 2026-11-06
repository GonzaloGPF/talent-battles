import useHttp from '@/composables/useHttp';
import { ref, watch } from 'vue';
import SelectItems from '@/objects/SelectItems';

export default (value, src, multiple = false, search = null, params = {}, urlAttribute = 'name') => {
    const { getJson, loading } = useHttp();
    src = ref(src);
    params = ref(params);
    search = ref(search);
    value = ref(value);
    multiple = ref(multiple);
    const items = ref([]);

    const selectedItems = ref(null);
    const selectionIcon = ref(null);
    let lastStringParams = null;

    watch(search, (newSearch) => {
        // if (! newSearch?.trim()) return;

        const paramsValue = params.value || {};

        if (newSearch) {
            paramsValue[urlAttribute] = newSearch;
        }

        makeRequest(src.value, paramsValue);
    });

    watch(params, (newParams) => {
        newParams = {...newParams};

        if (! newParams) return;

        if (value.value) {
            const paramsData = getPreParams(value.value, multiple.value, urlAttribute);

            newParams[paramsData.urlAttribute] = paramsData.urlValue;
        }

        makeRequest(src.value, newParams);
    }, { immediate: true });

    watch(value, (newValue, oldValue) => {
        if (newValue === oldValue) return;

        if (newValue) {
            selectedItems.value = SelectItems.selectedItems(items.value, newValue, multiple.value);
            selectionIcon.value = SelectItems.selectionIcon(items.value, newValue, multiple.value);
        } else {
            selectedItems.value = null;
            selectionIcon.value = null;
        }
    }, { immediate: true });

    function makeRequest(src, params = {}, force = false) {
        const stringParams = JSON.stringify(params || null);

        if (!force && lastStringParams === stringParams) {
            return Promise.resolve(items.value);
        }

        lastStringParams = stringParams;

        return getJson(src, { params })
            .then(({ data }) => items.value = data);
    }

    return {
        loading,
        items,
        selectedItems,
        selectionIcon,
    }
}

/**
 * Prepare params when prefilled
 */
function getPreParams(value, multiple, urlAttribute) {
    const urlValue = multiple
        ? Object.values(value)
        : value;

    const isConstant = multiple
        ? typeof urlValue[0] === 'string'
        : typeof urlValue === 'string';

    return {
        urlAttribute: isConstant ? urlAttribute : 'id',
        urlValue,
    }
}
