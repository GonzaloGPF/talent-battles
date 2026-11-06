<template>
  <v-container fluid>
    <docs-button
      path="trading"
      class="!md:absolute md:right-20"
    />
    <v-tabs
      :items="tabs"
      class="lg:min-w-min mb-3"
      fixed-tabs
      show-arrows
    />
    <app-router-view />
  </v-container>
</template>
<script setup>
import Translator from '@/objects/Translator';
import DocsButton from '@/components/buttons/DocsButton.vue';
import { computed } from 'vue';
import { useDisplay } from 'vuetify';
import AppRouterView from '@/components/core/AppRouterView.vue';

const { lg, xl, xxl } = useDisplay();
const isBigScreen = computed(() => (lg.value || xl.value || xxl.value));
const tabs = computed(() => [
  {
    title: Translator.modelTitle('product', true),
    text: isBigScreen.value ? Translator.modelTitle('product', true) : null,
    to: { name: 'admin.products.index' },
    prependIcon: '$product',
  },
  {
    title: Translator.modelTitle('order', true),
    text: isBigScreen.value ? Translator.modelTitle('order', true) : null,
    to: { name: 'admin.orders.index' },
    prependIcon: '$order',
  },
  {
    title: Translator.modelTitle('sale', true),
    text: isBigScreen.value ? Translator.modelTitle('sale', true) : null,
    to: { name: 'admin.sales.index' },
    prependIcon: '$sale',
  },
]);
</script>
