import { reactive, ref, unref } from 'vue';
import { useRoute } from 'vue-router';
import Filter from '@/objects/Filter';

export default (fields, isInternal, withTrash) => {
    const { query } = useRoute();
    isInternal = unref(isInternal);
    withTrash = unref(withTrash);
    fields = ref(fields);

    const inputs = Filter.processFields(fields.value, query, isInternal, withTrash);

    const inputEntries = inputs
        .map(input => [input.name, input.value]);

    const filterForm = reactive(Object.fromEntries(inputEntries));

    return {
        inputs,
        filterForm,
    }
}
