<template>
  <app-button
    :title="Translator.actionTitle('donate')"
    :loading="loading"
    :color="null"
    :modal="modal"
    icon="$donation"
    size="large"
    variant="text"
    @click="showModal = true"
  >
    <template #modal>
      <app-alert
        :message="Translator.t('help.make_a_donation')"
        border="start"
        variant="tonal"
      />
      <donation-form ref="formElement" />
    </template>
  </app-button>
</template>
<script setup>
import DonationForm from '@/components/forms/DonationForm.vue';
import Translator from '@/objects/Translator';
import {computed, ref} from 'vue';
import AppButton from '@/components/core/AppButton.vue';
import AppAlert from '@/components/core/AppAlert.vue';
import {useCart} from '@/stores/cart';
import {storeToRefs} from 'pinia';
import useHttp from '@/composables/useHttp';

const { getJson, loading: httpLoading } = useHttp();
const { addCartItem } = useCart();
const { loading } = storeToRefs(useCart());
const showModal = ref(false);
const formElement = ref(null);
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value || httpLoading.value,
    title: Translator.tl('make_donation'),
  },
  listeners: {
    close: () => showModal.value = false,
    ok: makeRequest,
  }
}));
const makeRequest = async () => {
  const validated = formElement.value?.validate();

  if (! validated) return;

  const donationProduct = await getDonationProduct();

  await addCartItem({
    product_id: donationProduct?.id,
    description: validated.message || Translator.t('help.doing_a_donation'),
    quantity: validated.amount,
  }, 'donation');

  showModal.value = false;
  formElement.value.reset();
}

const getDonationProduct = async () => {
  const { data } = await getJson('products', { params: { product_types: ['donation'] }});

  return data[0];
}
</script>
