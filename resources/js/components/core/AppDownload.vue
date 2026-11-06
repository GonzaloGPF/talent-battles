<template>
  <app-button
    :title="title || file?.name"
    :label="withText ? title || file?.name : null"
    :disabled="! file"
    :loading="loading"
    :icon="withText ? null : icon"
    :size="size"
    :variant="variant"
    @click="downloadFile(file, { stream })"
  />
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import AppButton from '@/components/core/AppButton.vue';

defineProps({
  title: {
    type: String,
    default: null
  },
  file: {
    type: Object,
    default: null
  },
  withText: {
    type: Boolean,
    default: false
  },
  icon: {
    type: String,
    default: 'mdi-download'
  },
  stream: {
    type: Boolean,
    default: false
  },
  size: {
    type: String,
    default: null
  },
  variant: {
    type: String,
    default: null
  },
});
const { downloadFile, loading } = useHttp();
</script>
