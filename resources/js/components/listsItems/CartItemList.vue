<template>
  <v-list-item>
    <template #prepend>
      <v-badge
        :content="isActive"
        :model-value="isActive > 0"
        :class="isActive ? 'cursor-pointer' : null"
        color="blue"
        @click.stop="onClick"
      >
        <app-icon
          :title="Translator.modelTitle('cart')"
          :color="isActive ? undefined : 'grey-darken-1'"
          icon="$cart"
        />
      </v-badge>
    </template>
    <v-list-item-title>
      <cart-menu
        ref="cartMenuElement"
        class="ml-3"
      />
    </v-list-item-title>
  </v-list-item>
</template>
<script setup>
import CartMenu from '@/components/menu/CartMenu.vue';
import { storeToRefs } from 'pinia';
import { useCart } from '@/stores/cart';
import AppIcon from '@/components/core/AppIcon.vue';
import Translator from '@/objects/Translator';
import { computed, ref } from 'vue';

const { cartItems } = storeToRefs(useCart());
const cartMenuElement = ref(null);

const isActive = computed(() => cartItems.value.length);

const onClick = () => {
  if (!isActive.value) {
    return;
  }
  cartMenuElement.value?.toggleMenu();
};
</script>