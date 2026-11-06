<template>
  <app-form>
    <v-progress-linear
      :active="loading"
      indeterminate
    />

    <i-text
      v-model="form.name"
      :errors="errors('name')"
      name="name"
    />

    <v-sheet
      class="p-3"
      color="grey-lighten-3"
      rounded="pill"
    >
      <!-- Stripe Elements Placeholder -->
      <div id="card-element" />
    </v-sheet>

    <span
      v-if="errorData"
      class="d-block red--text animate__animated animate__fadeIn"
      v-text="errorData"
    />
  </app-form>
</template>
<script setup>
import useFormValidator from '@/composables/useFormValidator';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import IText from '@/components/forms/inputs/iText.vue';
import AppForm from '@/components/core/AppForm.vue';
import Stripe from '@/objects/Stripe';
import { storeToRefs } from "pinia";
import { useAuth } from "@/stores/auth";

const { user } = storeToRefs(useAuth());
const errorData = ref(null);
const loading = ref(false);
const cardId = '#card-element';
let cardElement = null;

const { form, validate, errors, setForm } = useFormValidator({ name: user.value?.name}, {}, ['name']);

const mountStripe = async () => {
    cardElement = await Stripe.mountCardElement(cardId);
}

const createPaymentMethod = async () => {
    // if (! secret) return;

    loading.value = true

    const { paymentMethod, error } = await Stripe.createPaymentMethod(cardElement,
        form.name,
        user.value?.email,
        user.value?.phone,
        user.value?.address,
    );

    loading.value = false;

    if (error) {
        errorData.value = error.message;
        return null;
    }

    errorData.value = null;

    return paymentMethod;
}

const reset = () => {
    cardElement?.clear();
    form.name = null;
}

onMounted(() => mountStripe());

onBeforeUnmount(() => Stripe.unmount(cardId));

defineExpose({ createPaymentMethod, validate, reset, setForm });
</script>
