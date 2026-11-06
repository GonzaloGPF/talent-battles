<template>
  <app-badge
    v-if="cartItems.length"
    :content="cartItems.length"
    :model-value="cartItems.length > 0"
    color="blue"
  >
    <app-button
      :loading="loading"
      :title="Translator.modelTitle('cart')"
      :disabled="!cartItems.length"
      :size="xs ? 'small' : undefined"
      icon="$cart"
      variant="text"
    >
      <template #menu>
        <v-menu
          v-model="showMenu"
          activator="parent"
          offset-y
        >
          <cart-items-list
            v-if="cartItems.length"
            @remove-all="onRemoveAll"
            @open="openProduct"
            @delete="removeCartItems"
            @process-order="push({ name: 'carts.show' })"
          />
        </v-menu>
      </template>
    </app-button>
  </app-badge>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { useConfirm } from '@/stores/confirm';
import CartItemsList from '@/components/lists/CartItemsList.vue';
import { useCart } from '@/stores/cart';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useDisplay } from 'vuetify';
import AppBadge from '@/components/core/AppBadge.vue';

const { push } = useRouter();
const { showConfirm } = useConfirm();
const { cartItems, loading, openProduct } = storeToRefs(useCart());
const { removeCartItems, clean } = useCart();
const { xs } = useDisplay();

const showMenu = ref(false);

const onRemoveAll = async () => {
  const accept = await showConfirm(Translator.t('help.delete_all'));

  if (! accept) return;

  clean();
}

const toggleMenu = () => {
  showMenu.value = !showMenu.value;
}

defineExpose({
  toggleMenu,
});
</script>
