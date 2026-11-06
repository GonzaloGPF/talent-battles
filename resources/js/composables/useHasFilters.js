import { useRoute, useRouter } from 'vue-router';
import { ref, watch } from 'vue';
import _ from 'lodash';
import Utils from '@/objects/Utils';

export default (request) => {
    const route = useRoute();
    const router = useRouter();

    const filters = ref(Utils.removeEmpty(route.query));

    const updateQuery = (params = {}) => {
        params = Object.assign({}, params);

        delete params['with'];

        router.replace({
            query: Utils.removeEmpty(params)
        }).catch(() => {
        })
    }

    watch(filters, (newValue, oldValue) => {
        if (_.areEquals(newValue, oldValue)) return;

        updateQuery(newValue);

        // emit('filters');

        request();
    });

    return {
        filters,
    }
}
