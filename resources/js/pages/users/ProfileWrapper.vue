<template>
  <div>
    <v-card>
      <v-card-title>
        <v-toolbar flat rounded elevation="4">
          <v-toolbar-title :text="user?.name" />
          <v-divider vertical class="ml-5 mr-auto" />
          <app-field
            :model-value="Translator.tl('public_profile')"
            :route-data="{ name: 'users.show', params: { id: user?.id }}"
            color="blue"
            class="px-3"
          />
        </v-toolbar>
      </v-card-title>
      <v-card-text>
        <div class="d-flex flex-col flex-lg-row">
          <v-tabs
            :direction="isBigScreen ? 'vertical' : 'horizontal'"
            :items="tabs"
            class="lg:min-w-min"
            show-arrows
          >
            <template #tab="{ item }">
              <v-tab
                :prepend-icon="item.prependIcon"
                :text="item.text"
                :value="item.value"
              />
            </template>
          </v-tabs>
          <app-router-view />
        </div>
      </v-card-text>
    </v-card>
  </div>
</template>
<script setup>
import { computed } from 'vue';
import { useAuth } from '@/stores/auth';
import { useDisplay } from 'vuetify';
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';
import { storeToRefs } from 'pinia';
import AppRouterView from '@/components/core/AppRouterView.vue';

const { user } = storeToRefs(useAuth());
const { lg, xl, xxl } = useDisplay();
const isBigScreen = computed(() => (lg.value || xl.value || xxl.value));
const tabs = computed(() => [
  {
    title: Translator.tl('profile'),
    text: isBigScreen.value ? Translator.tl('profile') : null,
    to: { name: 'profile.show' },
    prependIcon: 'mdi-account',
  },
  {
    title: Translator.modelTitle('notification', true),
    text: isBigScreen.value ? Translator.modelTitle('notification', true) : null,
    to: { name: 'profile.notification' },
    prependIcon: '$notification',
  },
  {
    title: Translator.tl('payment_methods'),
    text: isBigScreen.value ? Translator.tl('payment_methods') : null,
    to: { name: 'profile.payment_methods' },
    prependIcon: '$payment_method',
  },
  {
    title: Translator.tl('account'),
    text: isBigScreen.value ? Translator.tl('account') : null,
    to: { name: 'profile.account' },
    prependIcon: '$sale',
  },
  {
    title: Translator.modelTitle('invitation', true),
    text: isBigScreen.value ? Translator.modelTitle('invitation', true) : null,
    to: { name: 'profile.invitations.index' },
    prependIcon: '$invitation',
  },
]);
</script>
