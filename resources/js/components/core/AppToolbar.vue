<template>
  <v-toolbar
    density="compact"
    :color="color"
    :elevation="elevation"
    flat
  >
    <v-toolbar-title>
      {{ title }}
    </v-toolbar-title>
    <slot />
    <v-spacer />
    <app-buttons
      :buttons="buttons"
      :loading="loading"
      :disabled="disabled"
      @click="$emit('click', $event)"
    />
  </v-toolbar>
</template>
<script setup>
import AppButtons from '@/components/core/AppButtons.vue';

defineEmits(['click']);
defineProps({
    title: {
        type: String,
        default: null
    },
    color: {
        type: String,
        default: null
    },
    elevation: {
        type: String,
        default: null
    },
    buttons: {
        type: Array,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
});
</script>
