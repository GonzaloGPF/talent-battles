<template>
  <app-button
    v-if="defaultPaymentMethod && totalAmount"
    :loading="loading"
    :label="Translator.tl('pay_now')"
    size="large"
    color="secondary"
    class="brand-font"
    @click="doPay"
  />
</template>
<script setup>
import { ref, watch } from 'vue';
import useHttp from '@/composables/useHttp';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import Translator from '@/objects/Translator';
import { useConfirm } from '@/stores/confirm';
import AppButton from '@/components/core/AppButton.vue';
import { useCart } from '@/stores/cart';
import Formatter from '@/objects/Formatter';
import { useRouter } from 'vue-router';

const { showConfirm } = useConfirm();
const { getJson, postJson, loading } = useHttp();
const { user, isLogged } = storeToRefs(useAuth());
const { totalAmount } = storeToRefs(useCart());
const { push } = useRouter();
const defaultPaymentMethod = ref(null);

async function getDefaultPaymentMethod() {
    if (! isLogged.value) {
        return;
    }

    if (defaultPaymentMethod.value) {
        return defaultPaymentMethod.value;
    }

    const { data } = await getJson('stripe/default_payment_methods', { quietly: true });

    return defaultPaymentMethod.value = data;
}

async function doPay() {
    const accept = await showConfirm(Translator.t('help.pay_with', {
        total: Formatter.money(totalAmount.value),
        name: Formatter.creditCard(defaultPaymentMethod.value?.card?.last4)
    }));

    if (! accept) return;

    const response = await postJson('pay_now');

    if (! response) return;

    if (response.code === 201) {
        const { data, secret } = response;

        goToSuccess(data, secret);
    }
}

function goToSuccess(order, secret) {
    push({
        name: 'orders.success.show',
        params: {
            id: order.id
        },
        query: {
            payment_intent: order.meta['payment_intent_id'],
            payment_intent_client_secret: secret
        }
    });
}

watch(user, getDefaultPaymentMethod, { immediate: true });
</script>
