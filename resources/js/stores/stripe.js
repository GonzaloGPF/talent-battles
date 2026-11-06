import { defineStore, storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import { ref } from 'vue';
import { loadStripe } from '@stripe/stripe-js/pure';
import global from '@/config/global';
import locales from '@/config/locales';

export const useStripe = defineStore('stripe', () => {
    const { user } = storeToRefs(useAuth());

    const stripe = ref(null);

    const getStripe = async() => {
        if (stripe.value) {
            return stripe.value;
        }

        return stripe.value = await loadStripe(global.env.stripeKey, {
            locale: user.value?.locale || locales.getLocale()
        });
    }

    return {
        getStripe,
    }
})
