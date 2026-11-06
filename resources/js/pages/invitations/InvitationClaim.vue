<template>
  <clean-layout
    :loading="loading"
    :title="Translator.tl('to_register')"
  >
    <app-alert
      :message="Translator.t('help.you_have_been_invited_by', { name: invitation?.createdBy?.name ?? ''})"
      variant="tonal"
      class="my-4 md:my-8 md:min-w-md"
    />
    <register-form
      ref="registerFormElement"
      @keyup.enter="submit"
    />
    <o-auth-buttons
      class="mt-5 mt-lg-1 min-w-xs"
      @redirected="onSuccess"
    />
    <app-button
      :label="Translator.actionTitle('register')"
      :loading="loading"
      :disabled="isLogged"
      size="large"
      color="info"
      class="brand-font mt-6 w-100"
      @click="submit"
    />
  </clean-layout>
</template>
<script setup>
import OAuthButtons from '@/components/buttons/OAuthButtons.vue';
import RegisterForm from '@/components/forms/RegisterForm.vue';
import { onMounted, ref } from 'vue';
import AppButton from '@/components/core/AppButton.vue';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import { useRouter } from 'vue-router';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import Formatter from '@/objects/Formatter';
import AppAlert from '@/components/core/AppAlert.vue';
import CleanLayout from '@/layouts/CleanLayout.vue';

const { loading, postJson } = useHttp();
const { setUser } = useAuth();
const { isLogged } = storeToRefs(useAuth());
const { push } = useRouter();

const registerFormElement = ref();
const invitation = ref(window.invitation);

const submit = () => {
  const data = registerFormElement.value?.validate();

  if (! data) return;

  postJson('register', data)
    .then(onSuccess)
}

const onSuccess = ({ data }) => {
  setUser(data);
  push({ name: 'welcome' });
};

const setForm = (invitation) => {
  const email = invitation.value?.email || '';
  const name = Formatter.ucFirst(email?.split('@')[0]);

  registerFormElement.value?.setForm({ email, name });
};

onMounted(async () => {
  setForm(invitation);
});
</script>