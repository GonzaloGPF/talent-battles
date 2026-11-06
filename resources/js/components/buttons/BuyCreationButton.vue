<template>
  <app-button
    v-if="isInSale"
    :title="Translator.actionTitle('buy')"
    :loading="loading"
    :color="color"
    :disabled="isDisabled"
    density="compact"
    icon="mdi-cart"
    size="small"
    variant="text"
    class="d-block"
    @click="onClick"
  />
</template>
<script setup>
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import { computed, toRef } from 'vue';
import { useAuth } from '@/stores/auth';
import { useCart } from '@/stores/cart';
import AppButton from '@/components/core/AppButton.vue';
import { useFlashMessages } from '@/stores/flashMessages';
import { storeToRefs } from 'pinia';
import Formatter from '@/objects/Formatter';

const props = defineProps({
  creation: {
    type: Object,
    default: null,
    required: true,
  },
});
const { loading } = useHttp();
const { pushFlashMessage } = useFlashMessages();
const { addCartItem, removeCartItems, isAlreadyInCart } = useCart();
const { user } = storeToRefs(useAuth());
const vCreation = toRef(props, 'creation');
const product = computed(() => vCreation.value?.product);
const isBought = computed(() => vCreation.value?.is_bought);
const isMine = computed(() => product.value?.created_by === user.value?.id);
const isSold = computed(() => product.value?.exclusive && !! product.value?.last_sale);
const isInSale = computed(() => !! product.value?.price);
const isDisabled = computed(() => isMine.value || isSold.value);
const productData = computed(() => ({
  product_id: vCreation.value?.product?.id,
  orderable_id: vCreation.value?.id,
  orderable_type: Formatter.model(vCreation.value?.model),
  quantity: 1,
}));
const color = computed(() => {
  if (isAlreadyInCart(productData.value)) {
    return 'blue';
  }

  if (isBought.value) {
    return 'green'
  }

  return null;
});

function onClick() {
  if (isAlreadyInCart(productData.value)) {
    return removeCartItems(productData.value?.product_id);
  }

  if (isBought.value) {
    return pushFlashMessage(Translator.t('validation.already_ordered'));
  }

  addCartItem(productData.value);
}
</script>
