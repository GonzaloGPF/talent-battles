<template>
  <div>
    <app-toolbar
      :buttons="buttons"
      :title="Translator.tl('address')"
      :loading="loading"
      elevation="4"
      @click="onClick"
    />

    <v-container @keyup.enter="onSave">
      <address-element-form
        v-if="isEditing"
        v-model="form"
        @ready="isReady = true"
        @complete="isComplete = $event"
      />
      <address-element-info
        v-else
        :model-value="user?.address"
        :phone="user?.phone"
      />
    </v-container>
  </div>
</template>
<script setup>
import AppToolbar from '@/components/core/AppToolbar.vue';
import { storeToRefs } from 'pinia';
import { useUser } from '@/stores/users';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import AddressElementForm from '@/components/forms/AddressElementForm.vue';
import AddressElementInfo from '@/components/info/AddressElementInfo.vue';
import { useFlashMessages } from '@/stores/flashMessages';

const { user, loading } = storeToRefs(useUser());
const { update, read } = useUser();
const { pushFlashMessage } = useFlashMessages();
const isEditing = ref(false);
const isReady = ref(false);
const isComplete = ref(false);
const form = ref({
  ...user.value?.address,
  phone: user.value?.phone
});
const buttons = computed(() => {
  return isEditing.value
    ? ['save', 'close']
    : ['edit'];
});

const onClick = (event) => {
  switch (event) {
    case 'edit':
      return isEditing.value = true;
    case 'save':
      return onSave();
    case 'close':
      return isEditing.value = false;
  }
}

function onSave() {
  if (! isComplete.value) {
    pushFlashMessage(Translator.tl('incomplete_address'), { type: 'error' });
    return;
  }

  update(user.value?.id, form.value)
    .then(onSuccess);
}

async function onSuccess() {
  isEditing.value = false;

  const data = await read(user.value?.id);

  form.value = {
    ...data.address,
    phone: data.phone
  }
}
</script>
