<template>
  <div class="w-100">
    <payment-form
      v-if="clientSecret"
      :client-secret="clientSecret"
      :email="lastEmail"
      class="my-10 min-w-md"
      @ready="isReady = true"
      @complete="isComplete = $event"
    />
    <app-button
      :disabled="isDisabled"
      :loading="loading"
      :label="clientSecret ? Translator.actionTitle('pay') : Translator.tl('pay_with')"
      size="large"
      color="info"
      class="brand-font mt-6 w-100"
      @click="processPayment"
    />
  </div>
</template>
<script setup>
import PaymentForm from '@/components/forms/PaymentForm.vue';
import AppButton from '@/components/core/AppButton.vue';
import { computed, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import usePaymentIntents from '@/composables/usePaymentIntents';
import { useCart } from '@/stores/cart';
import Translator from '@/objects/Translator';

const emits = defineEmits(['loading']);
const { cartItems } = storeToRefs(useCart());
const { clientSecret, loading, processPayment, lastEmail } = usePaymentIntents();
const isReady = ref(false);
const isComplete = ref(false);
const isDisabled = computed(() => {
    if (! cartItems.value?.length) return true;

    if (clientSecret.value) {
        return ! isReady.value || ! isComplete.value;
    }

    return loading.value;
});

watch(loading, value => emits('loading', value));
</script>
