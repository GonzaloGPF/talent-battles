<template>
  <div>
    <app-toolbar
      :buttons="isEditing ? ['save', 'close'] : ['edit', 'delete']"
      :title="user.nick_name"
      :loading="loading"
      color="grey-darken-3"
      elevation="4"
      @click="onClick"
    />

    <v-container>
      <profile-form
        v-if="isEditing"
        ref="formElement"
        v-model="form"
      />
      <profile-info
        v-else
        :model-value="user"
      />
    </v-container>
  </div>
</template>
<script setup>
import ProfileForm from '@/components/forms/ProfileForm.vue';
import ProfileInfo from '@/components/info/ProfileInfo.vue';
import { ref } from 'vue';
import { useUser } from '@/stores/users';
import { useRouter } from 'vue-router';
import AppToolbar from '@/components/core/AppToolbar.vue';
import { useConfirm } from '@/stores/confirm';
import { storeToRefs } from 'pinia';
import Utils from '@/objects/Utils';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';

const { user } = storeToRefs(useUser());
const { destroy, update, read } = useUser();
const { loading } = storeToRefs(useUser());
const { logout } = useAuth();
const { push } = useRouter();
const { showConfirm } = useConfirm();
const isEditing = ref(false);
const form = ref(Utils.copy(user.value, ['image', 'account', 'notification_settings', 'address']));
const formElement = ref(null);

const onClick = (event) => {
  switch (event) {
    case 'edit':
      return isEditing.value = true;
    case 'save':
      return onSave();
    case 'delete':
      return destroyUser();
    case 'close':
      return isEditing.value = false;
  }
}

async function onSave() {
  const valid = formElement.value.validate(true);

  if (! valid) return;

  await update(user.value.id, valid);

  isEditing.value = false;

  form.value = await read(user.value?.id);
}

async function destroyUser() {
  const accept = await showConfirm(Translator.t('help.destroy_user'));

  if (! accept) return;

  await destroy(user.value?.id);

  await logout();

  push({ name: 'welcome' });
}
</script>
