<template>
  <v-container>
    <ul v-if="accounts.length">
      <li
        v-for="(account, i) in accounts"
        :key="i"
        class="d-flex justify-space-between align-center w-full mb-2"
      >
        <v-card class="w-full" color="grey darken-3">
          <v-card-text class="d-flex justify-space-between w-full">
            <app-field
              :label="Translator.ta('country')"
              :model-value="account.country"
              class="mr-3 w-full"
            />
            <app-field
              :label="Translator.ta('email')"
              :model-value="account.email"
              class="mr-3 w-full"
            />
            <app-field
              :label="Translator.tl('details_submitted')"
              :model-value="account.details_submitted"
              type="boolean"
              class="mr-3 w-full"
            />
            <app-field
              :label="Translator.tl('payouts_enabled')"
              :model-value="account.payouts_enabled"
              type="boolean"
              class="mr-3 w-full"
            />
          </v-card-text>
        </v-card>

        <app-button
          v-if="editing"
          :title="Translator.actionTitle('delete')"
          :disabled="loading"
          size="small"
          icon="mdi-delete"
          @click="$emit('delete', account)"
        />
      </li>
    </ul>
    <app-empty v-else />
  </v-container>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';
import AppButton from '@/components/core/AppButton.vue';
import AppEmpty from '@/components/core/AppEmpty.vue';

defineEmits(['delete']);
defineProps({
    editing: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    accounts: {
        type: Array,
        default: () => []
    }
});
</script>
