<template>
  <div>
    <app-toolbar
      :buttons="['create', 'delete', 'help']"
      :title="Translator.tl('payment_methods')"
      :disabled="loading"
      elevation="4"
      @click="onClick"
    />

    <v-progress-linear
      :active="loading"
      indeterminate
    />

    <payment-method-list
      :loading="loading"
      :payment-methods="paymentMethods"
      :default-payment-method="defaultPaymentMethod"
      @default="onDefault"
      @delete="onDelete"
    />

    <app-modal
      :model-value="showModal"
      :title="Translator.actionTitle('create', 'payment_method')"
      :loading="loading"
      @ok="onCreate"
      @close="onClose"
    >
      <payment-method-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import PaymentMethodForm from '@/components/forms/PaymentMethodForm.vue';
import PaymentMethodList from '@/components/lists/PaymentMethodList.vue';
import { ref, watch } from 'vue';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import { useUser } from '@/stores/users';
import AppToolbar from '@/components/core/AppToolbar.vue';
import AppModal from '@/components/core/AppModal.vue';
import { useConfirm } from '@/stores/confirm';
import { storeToRefs } from 'pinia';
import Utils from '@/objects/Utils';
import Formatter from '@/objects/Formatter';
import useDocsButton from '@/composables/useDocsButton';

const { showConfirm } = useConfirm();
const paymentMethods = ref([]);
const { user } = storeToRefs(useUser());
const showModal = ref(false);
const defaultPaymentMethod = ref({});
const formElement = ref(null);
const { deleteJson, putJson, getJson, postJson, loading } = useHttp();
const { goToDoc } = useDocsButton('profile#payment-methods');

function onClick(event) {
  switch (event) {
    case 'create':
      return showModal.value = true;
    case 'delete':
      return destroyAll();
    case 'help':
      return goToDoc();
  }
}

async function destroyAll() {
  const accept = await showConfirm(Translator.t('help.delete_all'));

  if (! accept) return;

  return deleteJson('stripe/payment_methods/0')
    .then(getPaymentMethods);
}

async function onDelete(paymentMethod) {
  const accept = await showConfirm(Translator.t('help.remove_payment_method', { last4: Formatter.creditCard(paymentMethod.card.last4) }));

  if (! accept) return;

  return deleteJson(`stripe/payment_methods/${ paymentMethod.id }`)
    .then(getPaymentMethods);
}

async function onDefault(paymentMethod) {
  const accept = await showConfirm(Translator.t('help.set_default', { last4: paymentMethod.card.last4 }));

  if (! accept) return;

  return putJson(`stripe/default_payment_methods/${ paymentMethod.id }`)
    .then(getPaymentMethods);
}

async function onCreate() {
  const paymentMethod = await formElement.value?.createPaymentMethod();

  if (! paymentMethod) return;

  postJson('stripe/payment_methods', { payment_method_id: paymentMethod.id })
    .then(getPaymentMethods);
}

function getPaymentMethods() {
  getJson('stripe/payment_methods')
    .then(onSuccess)
    .then(getDefaultPaymentMethod);
}

function getDefaultPaymentMethod() {
  getJson('stripe/default_payment_methods', { quietly: true })
    .then(({ data }) => defaultPaymentMethod.value = data);
}

function onSuccess({ data }) {
  if (! data) {
    return resetForm();
  }

  if (! Array.isArray(data)) {
    data = [data];
  }

  paymentMethods.value = data;
  showModal.value = false;
  resetForm();
}

function onClose() {
  showModal.value = false;
  resetForm();
}

function resetForm() {
  formElement.value?.reset();
}

watch(user, (newValue, oldValue) => {
  if (Utils.areEquals(newValue, oldValue)) return;

  getPaymentMethods();
}, { immediate: true, deep: true });
</script>
