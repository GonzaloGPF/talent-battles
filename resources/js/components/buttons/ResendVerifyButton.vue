<template>
  <app-button
    v-if="isLogged && !user?.verified_at"
    :title="Translator.actionTitle('resend', 'email')"
    :loading="loading"
    :disabled="email?.retries >= 3"
    density="compact"
    icon="$email"
    size="small"
    variant="text"
    @click="onClick"
  />
</template>
<script setup>
import AppButton from '@/components/core/AppButton.vue';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import { useConfirm } from '@/stores/confirm';
import { ref } from 'vue';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import { useFlashMessages } from '@/stores/flashMessages';

const { loading, getJson, putJson } = useHttp();
const { showConfirm } = useConfirm();
const { pushFlashMessage } = useFlashMessages();
const { fetchMe } = useAuth();
const { isLogged, user } = storeToRefs(useAuth());
const email = ref();

async function onClick() {
  email.value = await getVerifyEmail();

  if (! email.value) {
    const noEmailText = Translator.t('exceptions.not_found_data', { data: Translator.modelTitle('email') });

    pushFlashMessage(noEmailText, { type: 'error' });

    return;
  }

  const accept = await showConfirm(Translator.tl('resend_verification_email', { email: email.value.to[0] }));

  if (! accept) return;

  putJson(`emails/${ email.value.id }/resend`)
    .then(onSuccess);
}

async function getVerifyEmail() {
  const { data } = await getJson('emails', { params: { email_type: ['verify_email'] } });

  const email = data[0];

  if (email.email_type !== 'verify_email') {
    return null;
  }

  return email;
}

function onSuccess({ data }) {
  email.value = data;
  fetchMe();
}
</script>