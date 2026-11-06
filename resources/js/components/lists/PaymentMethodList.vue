<template>
  <v-container>
    <v-row v-if="paymentMethods.length">
      <v-col
        v-for="(paymentMethod, i) in paymentMethods"
        :key="i"
        cols="12"
        md="6"
        lg="4"
        xl="2"
      >
        <payment-method-card
          :payment-method="paymentMethod"
          :loading="loading"
          :is-default="paymentMethod.id === defaultPaymentMethod?.id"
          :flag="user.address?.country_code"
          @default="$emit('default', paymentMethod)"
          @delete="$emit('delete', paymentMethod)"
        />
      </v-col>
    </v-row>
    <app-empty v-else />
  </v-container>
</template>
<script setup>
import AppEmpty from '@/components/core/AppEmpty.vue';
import { useAuth } from '@/stores/auth';
import PaymentMethodCard from '@/components/cards/PaymentMethodCard.vue';
import { storeToRefs } from 'pinia';

defineEmits([
    'default',
    'delete',
]);
defineProps({
    loading: {
        type: Boolean,
        default: false
    },
    paymentMethods: {
        type: Array,
        default: () => []
    },
    defaultPaymentMethod: {
        type: Object,
        default: null
    }
});
const { user } = storeToRefs(useAuth());
</script>
