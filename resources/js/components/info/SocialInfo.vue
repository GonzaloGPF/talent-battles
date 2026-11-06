<template>
  <div>
    <app-field
      label="Web"
      :model-value="modelValue?.web"
      type="link"
    />

    <app-field
      label="Facebook"
      :model-value="modelValue?.facebook"
      type="link"
    />

    <app-field
      label="Instagram"
      :model-value="modelValue?.instagram"
      type="link"
    />

    <app-field
      label="Youtube"
      :model-value="modelValue?.youtube"
      type="link"
    />

    <app-field
      label="Soundcloud"
      :model-value="modelValue?.soundcloud"
      type="link"
    />

    <app-field
      label="Soundclick"
      :model-value="modelValue?.soundclick"
      type="link"
    />

    <app-field
      label="Twitter"
      :model-value="modelValue?.twitter"
      type="link"
    />
  </div>
</template>
<script setup>
import AppField from '@/components/core/AppField.vue';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
</script>
