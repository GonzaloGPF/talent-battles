<template>
  <div>
    <app-toolbar
      :buttons="['save']"
      :title="Translator.tl('password_change')"
      :loading="loading"
      color="grey-darken-3"
      elevation="4"
      @click="onSave"
    />

    <v-container @keyup.enter="onSave">
      <password-form ref="formElement" />
    </v-container>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import useHttp from '@/composables/useHttp';
import { useUser } from '@/stores/users';
import AppToolbar from '@/components/core/AppToolbar.vue';
import { storeToRefs } from 'pinia';
import Translator from '@/objects/Translator';
import PasswordForm from '@/components/forms/PasswordForm.vue';

const { user } = storeToRefs(useUser());
const { putJson, loading } = useHttp();
const formElement = ref(null);

async function onSave() {
    const valid = formElement.value.validate();

    if (! valid) return;

    await putJson(`password/update/${user.value.id}`, valid);

    formElement.value.reset();
}
</script>
