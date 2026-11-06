<template>
  <v-app-bar app>
    <v-app-bar-nav-icon @click="$emit('left-menu-click', $event)">
      <app-icon
        :color="leftMenu ? 'blue' : 'white'"
        icon="mdi-menu"
      />
    </v-app-bar-nav-icon>

    <v-app-bar-title
      :title="title"
      :text="title"
      style="cursor: pointer"
      class="menu-title-brand text-base md:text-lg"
      @click="$router.push({ name: 'welcome' })"
    />

    <v-spacer />

    <language-menu />

    <CartMenu v-if="cartItems.length" />

    <ProfileMenu v-if="isLogged" />

    <EnterAppButton />

    <v-app-bar-nav-icon
      v-if="isLogged"
      class="-ml-1"
      @click="$emit('right-menu-click', $event)"
    >
      <AppIcon
        :color="rightMenu ? 'blue' : 'white'"
        icon="mdi-view-list"
      />
    </v-app-bar-nav-icon>
  </v-app-bar>
</template>
<script setup>
import LanguageMenu from '@/components/menu/LanguageMenu.vue';
import AppIcon from '@/components/core/AppIcon.vue';
import { storeToRefs } from 'pinia';
import EnterAppButton from '@/components/buttons/EnterAppButton.vue';
import { useTitle } from '@/stores/title';
import { useAuth } from '@/stores/auth';
import {useCart} from '@/stores/cart'
import {defineAsyncComponent} from 'vue'

defineEmits(['left-menu-click', 'right-menu-click']);
defineProps({
  leftMenu: {
    type: Boolean,
    default: false
  },
  rightMenu: {
    type: Boolean,
    default: false
  },
});
const CartMenu = defineAsyncComponent(() => import('@/components/menu/CartMenu.vue'))
const ProfileMenu = defineAsyncComponent(() => import('@/components/menu/ProfileMenu.vue'))
const { cartItems } = storeToRefs(useCart());
const { title } = storeToRefs(useTitle());
const { isLogged } = storeToRefs(useAuth());
</script>
