<template>
  <div>
    <app-toolbar
      :buttons="isEditing ? ['save', 'close'] : ['edit', 'help']"
      :title="Translator.modelTitle('notifications', true)"
      :loading="loading"
      elevation="4"
      @click="onClick"
    />

    <v-card-text @keyup.enter="onSave">
      <notification-setting-form
        v-if="isEditing"
        v-model="form"
      />
      <notification-setting-info
        v-else
        :model-value="user.notification_settings"
      />
    </v-card-text>
  </div>
</template>
<script setup>
import { ref } from 'vue';
import { useUser } from '@/stores/users';
import AppToolbar from '@/components/core/AppToolbar.vue';
import { storeToRefs } from 'pinia';
import Translator from '@/objects/Translator';
import NotificationSettingForm from '@/components/forms/NotificationSettingForm.vue';
import NotificationSettingInfo from '@/components/info/NotificationSettingInfo.vue';
import useDocsHelp from '@/composables/useDocsButton';

const { user, loading } = storeToRefs(useUser());
const { update, read } = useUser();
const isEditing = ref(false);
const form = ref(user.value?.notification_settings);
const { goToDoc } = useDocsHelp('notifications');

const onClick = (event) => {
    switch (event) {
        case 'edit':
            return isEditing.value = true;
        case 'save':
            return onSave();
        case 'close':
            return isEditing.value = false;
        case 'help':
            return goToDoc();
    }
}

async function onSave() {
    await update(user.value?.id, { notification_settings: form.value});

    isEditing.value = false;

    const data = await read(user.value?.id);

    form.value = data.notification_settings;
}
</script>
