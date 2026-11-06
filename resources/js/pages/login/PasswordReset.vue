<template>
  <clean-layout
    :loading="loading"
    :title="Translator.tl('set_password')"
  >
    <password-reset-form
      ref="form"
      class="login-form w-100"
    />
    <app-button
      :label="Translator.tl('set_password')"
      :loading="loading"
      size="large"
      color="info"
      class="brand-font mt-6 w-100"
      @click="submit"
    />
  </clean-layout>
</template>
<script setup>
import PasswordResetForm from '@/components/forms/PasswordResetForm.vue';
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import useHttp from '@/composables/useHttp';
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import CleanLayout from '@/layouts/CleanLayout.vue';

const form = ref(null);
const loading = ref(false);
const token = ref(null);
const { params } = useRoute();
const { push } = useRouter();
const { setUser } = useAuth();
const { postJson } = useHttp();

onMounted(() => token.value = params?.token);

function submit() {
  const data = form.value.validate();

  if (! data) return;

  postJson(`password/reset/${ token.value }`, data)
    .then(onSuccess);
}

function onSuccess({ data }) {
  setUser(data);
  push({ name: 'welcome' });
}
</script>
