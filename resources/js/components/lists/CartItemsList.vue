<template>
  <v-list
    v-if="cartItems.length"
    :items="products"
    density="compact"
    lines="two"
    class="max-w-lg min-w-xs"
    @update:selected="selected = $event"
  >
    <!-- TODO: remove this subtitle template -->
    <template #subtitle=" { item }">
      {{ item.subtitle }}
    </template>
    <template #append="{ item }">
      <app-button
        v-if="!['removeAll', 'process_order'].includes(item.value)"
        :title="Translator.actionTitle('read')"
        variant="text"
        size="small"
        icon="mdi-delete"
        @click.prevent="$emit('delete', findById(item.value))"
      />
    </template>
  </v-list>
  <app-empty v-else />
</template>
<script setup>
import { computed, ref, watch } from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import Formatter from "@/objects/Formatter";
import { useCart } from "@/stores/cart";
import { storeToRefs } from "pinia";
import AppEmpty from "@/components/core/AppEmpty.vue";

const emits = defineEmits([
    'removeAll',
    'processOrder',
    'open',
    'delete',
]);
const props = defineProps({
    withoutProcess: {
        type: Boolean,
        default: false
    }
});
const { cartItems, totalAmount } = storeToRefs(useCart());
const selected = ref(null);

const products = computed(() => {
    const allProducts = [{
        type: 'header',
        title: Translator.actionTitle('remove'),
        value: 'removeAll',
        props: {
            class: 'text-center'
        }
    }];

    cartItems.value
        ?.map(toProduct)
        .forEach(product => allProducts.push(product));

    if (! props.withoutProcess) {
        allProducts.push({
            title: Translator.tl('process_order', {total: Formatter.money(totalAmount.value)}),
            value: 'process_order',
            props: {
                class: 'text-center'
            }
        });
    }

    return allProducts;
});

const toProduct = cartItem => ({
    title: Translator.translate(cartItem.name),
    subtitle: Formatter.money(cartItem.total_with_taxes),
    value: cartItem.id,
});

const findById = id => cartItems.value?.find(notification => notification.id === id);

watch(selected, (value) => {
    value = value[0];

    if (value === 'removeAll') {
        return emits('removeAll');
    }

    if (value === 'process_order') {
        return emits('processOrder');
    }

    emits('open', findById(value));
})
</script>
