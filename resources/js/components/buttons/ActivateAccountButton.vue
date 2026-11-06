<template>
  <div v-if="! user?.account">
    <validation-errors class="my-3 rounded max-w-lg" />
    <app-button
      :loading="loading"
      :label="Translator.actionTitle('activate')"
      size="large"
      color="info"
      @click="onClick"
    />
  </div>
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import ValidationErrors from '@/components/core/ValidationErrors.vue';
import { computed, onMounted } from 'vue';
import { useFlashMessages } from '@/stores/flashMessages';

const emit = defineEmits(['success']);
const { loading, postJson } = useHttp();
const { user } = storeToRefs(useAuth());
const { pushFlashMessage } = useFlashMessages();
const address = computed(() => user.value?.address);
const addressCompleted = computed(() => !!(address.value?.country_code) && !!(address.value?.region_code) && !!(address.value?.city) && !!(address.value?.postcode));

function onClick() {
  if (! user.value?.verified_at) {
    pushFlashMessage(Translator.t('exceptions.unverified_email'), { type: 'error' });
    return;
  }

  if (! addressCompleted.value) {
    pushFlashMessage(Translator.tl('incomplete_address'), { type: 'error' });
    return;
  }

  makeRequest();
}

function makeRequest() {
    postJson('stripe/accounts')
        .then(() => emit('success'));
}

onMounted(() => {
    if (user.value?.account && !user.value?.is_account_ready) {
        makeRequest();
    }
})
</script>
