<template>
  <v-container fluid>
    <docs-button
      path="home"
      class="!absolute right-0"
    />
    <h2
      class="h3"
      v-text="Translator.t('help.home_title', { name: user?.name })"
    />

    <v-tabs
      :items="tabs"
      class="lg:min-w-min mb-8"
      fixed-tabs
      show-arrows
    />

    <app-router-view />
  </v-container>
</template>
<script setup>
import Translator from '@/objects/Translator';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import DocsButton from '@/components/buttons/DocsButton.vue';
import { computed } from 'vue';
import { useDisplay } from 'vuetify';
import AppRouterView from '@/components/core/AppRouterView.vue';
import {Chart, registerables} from 'chart.js';

Chart.register(...registerables);

const { user } = storeToRefs(useAuth());
const { lg, xl, xxl } = useDisplay();
const isBigScreen = computed(() => (lg.value || xl.value || xxl.value));
const tabs = computed(() => [
  {
    title: Translator.tl('summary'),
    text: isBigScreen.value ? Translator.tl('summary') : null,
    to: { name: 'stats.summary' },
    prependIcon: 'mdi-chart-pie',
  },
  {
    title: Translator.tl('balance'),
    text: isBigScreen.value ? Translator.tl('balance') : null,
    to: { name: 'stats.balance' },
    prependIcon: 'mdi-chart-line',
    props: {
      disabled: !user.value?.is_account_ready
    }
  },
]);
</script>
