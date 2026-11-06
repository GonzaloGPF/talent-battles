import { computed, ref, watch } from 'vue';
import useHttp from '@/composables/useHttp';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import EmailForm from '@/components/forms/EmailForm.vue';
import { useConfirm } from '@/stores/confirm';
import Stripe from '@/objects/Stripe';
import { useCart } from '@/stores/cart';
import Formatter from '@/objects/Formatter';
import { useErrors } from '@/stores/errors';

export default function () {
    const { postJson, putJson, loading: httpLoading } = useHttp();
    const { totalAmount, loading: cartLoading } = storeToRefs(useCart());
    const { showPrompt } = useConfirm();
    const { user } = storeToRefs(useAuth());
    const { setErrors } = useErrors();
    const isWorking = ref(false);
    const clientSecret = ref(null);
    const lastEmail = ref(null);
    const paymentIntent = ref(null);
    const errors = ref(null);

    const loading = computed(() => httpLoading.value || cartLoading.value || isWorking.value);

    const action = computed(() => {
        if (clientSecret.value) {
            return Translator.actionTitle('pay');
        } else {
            return Translator.tl('process_order', { total: Formatter.money(totalAmount.value) });
        }
    });

    async function processPayment() {
        if (! clientSecret.value) {
            await createPayment();
        } else {
            await confirmPayment();
        }
    }

    async function createPayment() {
        const email = await getEmail();

        if (! email) return;

        clientSecret.value = null;

        const { data } = await postJson('stripe/payment_intents', {
            email,
            amount: totalAmount.value,
        });

        return clientSecret.value = data?.client_secret;
    }

    async function updatePayment() {
        const paymentIntentId = await getPayment();

        if (! paymentIntentId) {
            return;
        }

        const { data } = await putJson(`stripe/payment_intents/${paymentIntentId}`, {
            amount: totalAmount.value,
        });

        return clientSecret.value = data?.client_secret;
    }

    async function confirmPayment() {
        errors.value = null;

        const email = lastEmail.value;

        if (! email) {
            return;
        }

        const paymentIntentId = await getPayment();

        if (! paymentIntentId) {
            return;
        }

        const { return_url } = await postJson('orders', {
            email,
            payment_intent_id: paymentIntentId
        });

        isWorking.value = true;

        const errorMsg = await Stripe.confirmPayment(email, return_url);

        errors.value = {
            stripe_error: [errorMsg]
        };

        isWorking.value = false;
    }

    async function getPayment() {
        if (paymentIntent.value) {
            return paymentIntent.value;
        }

        if (! clientSecret.value) {
            return;
        }

        isWorking.value = true;

        const { id } = await Stripe.retrievePaymentIntent(clientSecret.value);

        isWorking.value = false;

        return paymentIntent.value = id;
    }

    async function getEmail() {
        if (lastEmail.value) {
            return lastEmail.value;
        }
        return lastEmail.value = user.value?.email || await askEmail();
    }

    function askEmail() {
        return showPrompt(Translator.t('help.we_need_your_email'), {
            title: Translator.tl('required_information'),
            component: EmailForm
        }).then(({ email }) => Promise.resolve(email))
            .catch(() => Promise.resolve(false));
    }

    watch(totalAmount, updatePayment);

    watch(errors, setErrors);

    return {
        action,
        loading,
        clientSecret,
        errors,
        processPayment,
        createPayment,
        confirmPayment,
        lastEmail,
    }
}
