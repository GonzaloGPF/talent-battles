<template>
  <v-container fluid>
    <app-alert
      :message="message"
      :type="alertType"
      class="mb-2"
      border="start"
    />
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <v-sheet
      elevation="10"
      class="p-5"
    >
      <h1
        class="subtitle-1"
        v-text="Translator.tl('order_completed')"
      />
      <p
        class="body-2 p-3"
        v-text="Translator.t('help.order_completed_body')"
      />
      <order-details
        v-if="isLogged"
        :model-id="parseInt(params.id)"
        :payment-intent-id="query.payment_intent"
        class="mt-3"
      />
      <app-field
        v-if="isLogged"
        :model-value="Translator.tl('go_to_my_orders')"
        :route-data="{ name: 'admin.orders.index'}"
        color="blue"
        class="px-3 text-center"
      />
    </v-sheet>
  </v-container>
</template>
<script setup>
import { useAuth } from '@/stores/auth'
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import Translator from '@/objects/Translator';
import AppAlert from '@/components/core/AppAlert.vue';
import { storeToRefs } from 'pinia';
import Stripe from "@/objects/Stripe";
import { useCart } from '@/stores/cart';
import OrderDetails from '@/components/tables/details/OrderDetails.vue';
import AppField from '@/components/core/AppField.vue';

const { params, query } = useRoute();
const { clean } = useCart();
const { isLogged } = storeToRefs(useAuth());
const message = ref(Translator.tl('please_wait'));
const loading = ref(false);
const alertType = ref(null);

onMounted(async () => {
    loading.value = true;

    await clean(true);

    await retrievePaymentIntent();

    loading.value = false;
});

async function retrievePaymentIntent() {
    const clientSecret = query.payment_intent_client_secret;

    if (! clientSecret) {
        message.value = Translator.t('help.could_not_check_payment_status');
        alertType.value = 'error';
        return;
    }

    const response = await Stripe.retrievePaymentIntent(clientSecret);

    message.value = response.message;
    alertType.value = response.type;
}
</script>
