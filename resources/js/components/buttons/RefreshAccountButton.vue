<template>
  <app-button
    v-if="user?.account"
    :loading="loading"
    :label="Translator.actionTitle('refresh')"
    :tooltip="Translator.t('help.refresh_account')"
    prepend-icon="mdi-refresh"
    @click="makeRequest"
  />
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';

const emits = defineEmits(['success']);
const { user } = storeToRefs(useAuth());
const { loading, postJson } = useHttp();

const makeRequest = () => {
    postJson('stripe/accounts')
        .then(() => emits('success'));
}
</script>
