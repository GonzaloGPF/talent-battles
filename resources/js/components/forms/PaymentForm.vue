<template>
  <app-form>
    <div id="link-authentication-element">
      <!--Stripe.js injects the Link Authentication Element-->
    </div>
    <div id="payment-element">
      <!-- Elements will create form elements here -->
    </div>
  </app-form>
</template>
<script setup>
import AppForm from '@/components/core/AppForm.vue';
import { onBeforeUnmount, toRef, watchEffect } from 'vue';
import Stripe from '@/objects/Stripe';

const emits = defineEmits(['ready', 'complete']);
const props = defineProps({
    clientSecret: {
        type: String,
        default: null
    },
    email: {
        type: String,
        default: null
    }
});
const clientSecret = toRef(props, 'clientSecret');
const email = toRef(props, 'email');
const linkId = '#link-authentication-element';
const paymentId = '#payment-element';

const mountPaymentElement = async() => {
    const element = await Stripe.mountPaymentElement(clientSecret.value, paymentId);

    element.on('ready', () => emits('ready'));
    element.on('change', data => emits('complete', data.complete));
}

const mountLinkAuthentication = () => {
    Stripe.mountLinkAuthentication(clientSecret.value, linkId, email.value);
}

watchEffect(async () => {
    if (clientSecret.value && email.value) {
        await mountPaymentElement();
        await mountLinkAuthentication();
    }
})

onBeforeUnmount(() => {
    Stripe.unmount(paymentId);
    Stripe.unmount(linkId);
});
</script>
