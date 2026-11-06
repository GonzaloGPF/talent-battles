<template>
  <app-form>
    <div id="address-element">
      <!--Stripe.js injects the Address Element-->
    </div>
  </app-form>
</template>
<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import AppForm from '@/components/core/AppForm.vue';
import Stripe from '@/objects/Stripe';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import useHttp from '@/composables/useHttp';
import AddressHelper from '@/helpers/AddressHelper';

const emits = defineEmits(['update:modelValue', 'ready', 'complete']);
defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const { user } = storeToRefs(useAuth());
const { getJson } = useHttp();
const countries = ref([]);
const addressId = '#address-element';

async function mountAddressElement() {
    const codes = countries.value?.map(country => country.code);

    const element = await Stripe.mountAddressElement(addressId, user.value, codes);

    element.on('ready', () => emits('ready'));
    element.on('change', data => {
        emits('update:modelValue', {
            address: AddressHelper.stripeToAddress(data.value.address),
            phone: data.value.phone,
        });
        emits('complete', data.complete);
    });
}

async function getCountries() {
    const { data } = await getJson('countries');
    countries.value = data;
}

onMounted(async() => {
    await getCountries();
    await mountAddressElement();
});

onBeforeUnmount(() => {
    Stripe.unmount(addressId);
});
</script>
