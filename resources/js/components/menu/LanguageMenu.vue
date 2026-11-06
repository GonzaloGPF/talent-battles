<template>
  <app-button
    :title="Translator.tl('language')"
    :loading="loading"
    :icon="currentFlag.icon"
    :size="xs ? 'small' : undefined"
  >
    <template #menu>
      <v-menu
        v-model="showMenu"
        offset-y
        activator="parent"
      >
        <LanguageList
          v-if="showMenu"
          :flags-data="flagsData"
          @click="onFlagClick"
        />
      </v-menu>
    </template>
  </app-button>
</template>
<script setup>
import { useAuth } from '@/stores/auth';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import Translator from '@/objects/Translator';
import { useDisplay } from 'vuetify';
import {defineAsyncComponent, ref} from 'vue'

const LanguageList = defineAsyncComponent(() => import('@/components/lists/LanguageList.vue'))

const { flagsData, currentFlag, loading } = storeToRefs(useAuth());
const { changeLocale } = useAuth();
const { xs } = useDisplay();

const showMenu = ref(false);

const onFlagClick = (locale) => changeLocale(locale);
</script>
