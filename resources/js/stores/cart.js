import { defineStore, storeToRefs } from 'pinia';
import useHttp from '@/composables/useHttp';
import { computed, ref, watch } from 'vue';
import Formatter from '@/objects/Formatter';
import CreationHelper from '@/helpers/CreationHelper';
import { useRouter } from 'vue-router';
import { useAuth } from '@/stores/auth';

export const useCart = defineStore('cart', () => {
    const { getJson, putJson, loading } = useHttp();
    const { push } = useRouter();
    const { user } = storeToRefs(useAuth());
    const backup = ref([]);
    let loaded = false;

    const cart = ref(null);

    const cartItems = computed(() => cart.value?.items || []);

    const totalAmount = computed(() => cartItems.value.reduce((total, item) => item.total_with_taxes + total, 0));

    async function fetch() {
        const { data } = await getJson('carts', { quietly: true });

        cart.value = data;
    }

    async function addCartItem(item) {
        if (isAlreadyInCart(item)) {
            return null;
        }

        const { data } = await putJson('carts', { items_data: [item] });

        cart.value = data;
    }

    async function addCartItems(items) {
        if (!Array.isArray(items)) {
            return;
        }

        const { data } = await putJson('carts', { items_data: items });

        cart.value = data;
    }

    async function removeCartItems(productIds) {
        if (typeof productIds === 'object') {
            productIds = [productIds.product_id];
        }
        if (! Array.isArray(productIds)) {
            productIds = [productIds];
        }

        const { data } = await putJson('carts', {
            deleting: true,
            product_ids: productIds,
        });

        cart.value = data;
    }

    async function clean(quietly) {
        backup.value = cart.value;

        const productIds = cart.value?.items?.map(item => item.product_id);

        const { data } = await putJson('carts', {
            deleting: true,
            product_ids: productIds,
        }, { quietly });

        cart.value = data;
    }

    function recover() {
        cart.value = backup.value;
    }

    async function openItem(item) {
        if (! ['design', 'track'].includes(item.product_type)) {
            return;
        }

        const orderableType = item.orderable_type
            .split('\\')
            .pop()
            .toLowerCase();

        const baseUrl = Formatter.plural(orderableType);

        const { data } = await getJson(`${ baseUrl }/${ item.orderable_id }`);

        push(CreationHelper.getCreationShowRoute(data));
    }

    function isAlreadyInCart(newItem) {
        return !! cartItems.value?.find(item => isSame(item, newItem));
    }

    watch(user, (newValue, oldValue) => {
        if (! loaded) {
            loaded = true;
            return fetch();
        }

        if (newValue?.id === oldValue?.id) {
            return;
        }

        fetch();
    }, { immediate: true });

    return {
        loading,
        totalAmount,
        cart,
        cartItems,
        clean,
        fetch,
        addCartItem,
        addCartItems,
        removeCartItems,
        isAlreadyInCart,
        recover,
        openItem,
    }
});

function isSame(item, newItem) {
    return item.id === newItem.id
        && item.orderable_id === newItem.orderable_id
        && item.orderable_type === newItem.orderable_type;
}