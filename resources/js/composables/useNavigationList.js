import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';

export default (items) => { // TODO: avoid using this composable if items has not unique 'value' field
    const { push, currentRoute } = useRouter();
    items = ref(items);

    const selectedValue = computed(() => [currentRoute.value.name])

    const navigate = (payload) => {
        const selectedValue = payload[0];

        const item = items.value.find(i => i.value === selectedValue);

        if (! item) return;

        if (typeof item.value === 'function') {
            return item.value();
        }

        push({
            name: item.value,
            params: item.routeParams,
        });
    }

    return {
        selectedValue,
        navigate
    }
}
