<template>
  <div>
    <app-alert
      density="compact"
      type="warning"
      variant="tonal"
      border="start"
      v-bind="$attrs"
    >
      <div class="d-flex align-center justify-space-around">
        <span v-text="Translator.tl('verification_pending')" />
        <resend-verify-button />
      </div>
    </app-alert>
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppAlert from '@/components/core/AppAlert.vue';
import ResendVerifyButton from '@/components/buttons/ResendVerifyButton.vue';
</script>
