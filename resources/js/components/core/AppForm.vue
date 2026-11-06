<template>
  <v-form>
    <validation-errors
      v-if="! hideFeedback"
      class="mt-1 mb-3 rounded"
    />
    <slot />
  </v-form>
</template>
<script setup>
import ValidationErrors from '@/components/core/ValidationErrors.vue';

defineProps({
    hideFeedback: {
        type: Boolean,
        default: false
    },
});
</script>
