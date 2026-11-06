<template>
  <v-container fluid>
    <app-logo />
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <v-sheet class="p-3 md:p-10 flex flex-column align-center justify-center items-center mt-6 animate__animated animate__fadeIn max-w-xl mx-auto">
      <h2
        class="brand-font mb-6 text-h4 text-center"
        v-text="title"
      />
      <slot />
    </v-sheet>
  </v-container>
</template>
<script setup>
import AppLogo from '@/components/core/AppLogo.vue';

defineProps({
  loading: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: null
  },
});
</script>