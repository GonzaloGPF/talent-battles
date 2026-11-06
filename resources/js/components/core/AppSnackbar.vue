<template>
  <v-snackbar
    v-model="isShowing"
    :color="snackParams.color"
    :timeout="snackParams.timeout"
    :multi-line="snackParams.multiline"
    :rounded="snackParams.rounded"
    :variant="snackParams.variant"
    :location="snackParams.location"
  >
    <div
      v-if="snackParams.title"
      class="text-subtitle-1 pb-2"
      v-text="snackParams.title"
    />

    <p v-text="snackbarMessage" />

    <template #actions>
      <app-button
        v-if="actionButton"
        :color="actionButton?.color"
        :variant="actionButton?.variant"
        :icon="actionButton?.icon"
        :title="actionButton?.title"
        :label="actionButton?.label"
        @click="actionButton?.onClick"
      />
    </template>
  </v-snackbar>
</template>
<script setup>
import { storeToRefs } from 'pinia';
import { useSnackbar } from '@/stores/snackbar';
import AppButton from '@/components/core/AppButton.vue';

const { snackbarMessage, isShowing, snackParams, actionButton } = storeToRefs(useSnackbar());
</script>