<template>
  <app-button
    v-if="user?.is_account_ready"
    :label="Translator.tl('billing_portal')"
    append-icon="mdi-open-in-new"
    variant="tonal"
    @click="Utils.openTab('billing-portal')"
  />
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import Utils from '@/objects/Utils';

const { user } = storeToRefs(useAuth());
</script>
