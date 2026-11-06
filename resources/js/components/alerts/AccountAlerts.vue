<template>
  <div v-if="isLogged && alertText">
    <app-alert
      density="compact"
      type="info"
      variant="tonal"
      border="start"
      v-bind="$attrs"
    >
      <div class="d-flex align-center justify-space-around">
        <span v-text="alertText" />
        <app-button
          v-if="$route.name !== 'profile.account'"
          :title="Translator.tl('account')"
          icon="$profile"
          variant="text"
          @click="onNavigate"
        />
      </div>
    </app-alert>
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppAlert from '@/components/core/AppAlert.vue';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import { computed } from 'vue';
import { useRouter } from 'vue-router';

const emits = defineEmits(['navigate']);

const { isLogged, user } = storeToRefs(useAuth());
const { push } = useRouter()

const alertText = computed(() => {
    if (! user.value?.account) {
        return Translator.t('help.activate_an_account');
    }

    if (! user.value?.is_account_ready) {
        return Translator.t('help.configure_account');
    }

    if (user.value?.is_account_ready) {
        return Translator.t('help.account_ready');
    }

    return null;
});

const onNavigate = () => {
    emits('navigate');
    push({ name: 'profile.account'});
}
</script>
