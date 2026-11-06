<template>
  <!-- v-if="!user?.account?.charges_enabled" -->
  <app-button
    v-if="user?.account"
    :loading="loading"
    :label="Translator.actionTitle('configure')"
    color="info"
    variant="outlined"
    @click="makeRequest"
  />
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { useConfirm } from '@/stores/confirm';
import Utils from '@/objects/Utils';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';

const { loading, postJson } = useHttp();
const { showConfirm } = useConfirm();
const { user } = storeToRefs(useAuth());

async function makeRequest () {
    const accept = await showConfirm(Translator.t('help.configure_account_link'));

    if (! accept) return;

    const { data } = await postJson('stripe/accounts/link');

    if (data.link) {
        Utils.openTab(data.link);
    }
}
</script>
