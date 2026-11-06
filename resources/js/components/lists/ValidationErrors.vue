<template>
  <app-alert
    v-if="hasErrors"
    :title="Translator.t('exceptions.validation')"
    type="error"
    variant="tonal"
    density="compact"
    class="pb-0"
    border="start"
  >
    <validation-errors-list :errors="errors" />
  </app-alert>
</template>
<script setup>
import { useErrors } from '@/stores/errors';
import { storeToRefs } from 'pinia';
import ValidationErrorsList from './ValidationErrorsList.vue';
import AppAlert from '@/components/core/AppAlert.vue';
import Translator from '@/objects/Translator';

const { errors, hasErrors } = storeToRefs(useErrors());
</script>
