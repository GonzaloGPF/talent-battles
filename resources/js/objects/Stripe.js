import { loadStripe } from '@stripe/stripe-js/pure';
import global from '@/config/global';
import locales from '@/config/locales';
import AddressHelper from '@/helpers/AddressHelper';

let stripeInstance;
let stripeElements;
let clientSecret;
const mountedElements = {};
const types = {
    payment: 'payment',
    linkAuthentication: 'linkAuthentication',
    card: 'card',
    address: 'address',
}

const messageByStatus = {
    succeeded: 'Payment succeeded!',
    processing: 'Your payment is processing.',
    requires_payment_method: 'Your payment was not successful, please try again.',
}

const typeByStatus = {
    succeeded: 'success',
    processing: 'warning',
    requires_payment_method: 'error',
}

const Stripe = {
    async getInstance() {
        if (stripeInstance) {
            return stripeInstance;
        }

        stripeInstance = await loadStripe(global.env.stripeKey, {
            locale: locales.getLocale()
        });

        return stripeInstance;
    },

    async getStripeElements(secret) {
        if (stripeElements && clientSecret) {
            return stripeElements;
        }

        if (secret) {
            clientSecret = secret;
        }

        const stripe = await Stripe.getInstance();

        stripeElements = stripe.elements({
            clientSecret,
            loader: 'auto',
            appearance: {
                theme: 'night',
                labels: 'floating'
            }});

        return stripeElements;
    },

    async mountPaymentElement(clientSecret, id) {
        return getOrCreateAndMount(id, types.payment, clientSecret);
    },

    async mountLinkAuthentication(clientSecret, id, email) {
        return getOrCreateAndMount(id, types.linkAuthentication, clientSecret, { defaultValues: { email } });
    },

    async mountCardElement(id) {
        return getOrCreateAndMount(id, types.card);
    },

    async mountAddressElement(id, user, countryCodes = []) {
        return getOrCreateAndMount(id, types.address, null, {
            mode: 'billing', // or 'shipping'
            // autocomplete: { // when not used with PaymentElements
            //     mode: "google_maps_api", // ‘automatic’ | ‘disabled’ | ‘google_maps_api’
            //     apiKey: "{YOUR_GOOGLE_MAPS_API_KEY}",
            // },
            allowedCountries: countryCodes.map(code => code.toUpperCase()),
            defaultValues: {
                name: user.name,
                // firstName: user.name,
                // lastName: user.last_name,
                phone: user.phone,
                address: AddressHelper.addressToStripe(user.address) || {},
            },
            // contacts: [
            //     {
            //         name: user.name,
            //         phone: user.phone,
            //         address
            //     }
            // ],
            fields: {
                phone: 'always', // 'always' | 'auto' | 'never'
            },
            // display: {
            //     name: 'split' // 'full' | 'split' | 'organization''
            // }
        });
    },

    /**
     *
     * @param {HTMLElement} cardElement
     * @param {string|null} name
     * @param {string|null} email
     * @param {string|null} phone
     * @param {array|null} address
     */
    async createPaymentMethod(cardElement, name, email, phone, address) {
        const stripe = await Stripe.getInstance();

        return stripe
            .createPaymentMethod({
                type: 'card',
                card: cardElement,
                billing_details: {
                    name,
                    email,
                    phone,
                    address: AddressHelper.addressToStripe(address)
                },
            });
    },

    // When Stripe redirects the customer to the return_url, the payment_intent_client_secret query parameter is appended by Stripe.js
    async confirmPayment(email, returnUrl) {
        if (! stripeInstance || ! returnUrl) return 'An unexpected error occurred.';

        const { error } = await stripeInstance.confirmPayment({
            elements: stripeElements,
            confirmParams: {
                return_url: returnUrl, // after payment completion page
                receipt_email: email,
            },
        });

        // This point will only be reached if there is an immediate error when
        // confirming the payment. Otherwise, your customer will be redirected to
        // your `return_url`. For some payment methods like iDEAL, your customer will
        // be redirected to an intermediate site first to authorize the payment, then
        // redirected to the `return_url`.
        if (error?.type === "card_error" || error?.type === "validation_error") {
            return error?.message;
        }
    },

    /**
     *
     * @param paymentIntentSecret
     * @param cardElement
     * @param billingDetails {
     *      card: HTMLElement,
     *      billing_details: {
     *          name: string,
     *          phone: string,
     *          address: {
     *              city: string,
     *              country: string,
     *              line1: string,
     *              line2: string,
     *              postal_code: string,
     *              state: string,
     *          }
     *      },
     * }
     * @returns {Promise<*|PaymentIntentResult>}
     */
    async confirmCardPayment(paymentIntentSecret, cardElement, billingDetails) {
        const stripe = await Stripe.getInstance();

        return stripe
            .confirmCardPayment(paymentIntentSecret, {
                payment_method: {
                    card: cardElement,
                    //payment_method: paymentMethodId
                    billing_details: billingDetails,
                },
            });
    },

    /**
     *
     * @param secret
     * @returns {Promise<{message: (*|string), type: (*|string), status}>}
     */
    async retrievePaymentIntent(secret) {
        const stripe = await Stripe.getInstance();

        const { paymentIntent } = await stripe.retrievePaymentIntent(secret ?? clientSecret);

        return {
            id: paymentIntent.id,
            status: paymentIntent.status,
            message: messageByStatus[paymentIntent.status] || 'Something went wrong.',
            type: typeByStatus[paymentIntent.status] || 'error',
        }
    },

    unmount(id) {
        if (! id) {
            return Object.values(mountedElements)
                .forEach(elementId => mountedElements[elementId].unmount())
        }
        return mountedElements[id]?.unmount();
    }
}

async function getOrCreateAndMount(id, type, secret, options) {
    const element = await getOrCreateElement(type, secret, options);

    element.mount(id);

    mountedElements[id] = element;

    return element;
}

async function getOrCreateElement(type, secret, options) {
    const elements = await Stripe.getStripeElements(secret);

    const element = elements.getElement(type);

    if (element) {
        return element
    }

    return elements.create(type, options);
}

export default Stripe;
