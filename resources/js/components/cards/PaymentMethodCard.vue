<template>
  <v-card
    :title="paymentMethod.card.brand"
    :subtitle="paymentMethod.card.exp_month + '/' + paymentMethod.card.exp_year"
    rounded="large"
    border="10"
    density="compact"
    color="indigo-darken-4 w-full"
    elevation="6"
    prepend-icon="mdi-credit-card"
  >
    <template #append>
      <app-icon :icon="flag" />
    </template>
    <v-card-text>
      <div class="d-flex flex-column">
        <span class="text-caption" v-text="paymentMethod.billing_details.name" />
        <span class="text-h6" v-text="Formatter.creditCard(paymentMethod.card.last4)" />
      </div>
    </v-card-text>
    <v-card-actions class="justify-end">
      <app-button
        :title="isDefault ? Translator.tl('default') : Translator.actionTitle('set_default')"
        :disabled="loading"
        :icon="isDefault ? 'mdi-star' : 'mdi-star-outline'"
        :color="isDefault ? 'green' : null"
        @click="isDefault ? null : $emit('default', paymentMethod)"
      />
      <app-button
        :title="Translator.actionTitle('delete')"
        :disabled="loading"
        icon="mdi-delete"
        @click="$emit('delete', paymentMethod)"
      />
    </v-card-actions>
  </v-card>
</template>
<script setup>
import AppButton from '@/components/core/AppButton.vue';
import AppIcon from '@/components/core/AppIcon.vue';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

defineEmits(['default', 'delete']);
defineProps({
    paymentMethod: {
        type: Object,
        default: null
    },
    flag: {
        type: String,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    },
    isDefault: {
        type: Boolean,
        default: false
    }
});
</script>
