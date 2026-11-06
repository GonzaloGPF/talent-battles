<template>
  <app-button
    v-if="isLogged"
    :label="title"
    :loading="loading"
    :modal="modal"
    prepend-icon="mdi-star"
    @click="onOpenModal"
  >
    <template #modal>
      <docs-button
        path="promo"
        class="float-right"
      />
      <promo-timeline
        :step="step"
        :steps="steps"
      />
      <product-promo-form
        ref="formElement"
        v-model="form"
        :step="step"
        class="max-w-4xl mx-auto"
      >
        <simple-table
          :headers="headers"
          :items="summaryProducts"
        />
      </product-promo-form>
    </template>
  </app-button>
</template>
<script setup>
import ProductPromoForm from '@/components/forms/ProductPromoForm.vue';
import { computed, ref } from 'vue';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import PromoTimeline from '@/components/timelines/PromoTimeline.vue';
import { useCompany } from '@/stores/company';
import Calculator from '@/objects/Calculator';
import SimpleTable from '@/components/tables/SimpleTable.vue';
import headers from '@/components/tables/headers/cartItemHeaders';
import { useConfirm } from '@/stores/confirm';
import { useCart } from '@/stores/cart';
import Time from '@/objects/Time';
import Formatter from '@/objects/Formatter';
import DocsButton from '@/components/buttons/DocsButton.vue';

const { loading, getJson } = useHttp();
const { showConfirm } = useConfirm();
const { addCartItems } = useCart();
const { promoData } = storeToRefs(useCompany());
const { isLogged } = storeToRefs(useAuth());
let promoProductId = null;
const STEPS = {
  CREATIONS: 0,
  MONTHS: 1,
  SUMMARY: 2
}
const form = ref({});
const formElement = ref(null);
const showModal = ref(false);
const step = ref(STEPS.CREATIONS);
const steps = computed(() => [
  {
    step: STEPS.CREATIONS,
    title: Translator.tl('products'),
    icon: 'mdi-file-outline',
    description: Translator.t('help.select_products_to_promo'),
    okText: Translator.actionTitle('continue'),
    cancelText: Translator.actionTitle('cancel'),
    canNext: () => form.value?.product_ids?.length > 0
  },
  {
    step: STEPS.MONTHS,
    title: Translator.ta('months'),
    icon: 'mdi-calendar',
    description: Translator.t('help.select_months_to_promo'),
    okText: Translator.actionTitle('continue'),
    cancelText: Translator.actionTitle('backward'),
    canNext: () => form.value?.months > 0
  },
  {
    step: STEPS.SUMMARY,
    title: Translator.tl('summary'),
    icon: 'mdi-check-all',
    description: Translator.t('help.promo_summary'),
    okText: Translator.actionTitle('finish'),
    cancelText: Translator.actionTitle('backward'),
    canNext: () => form.value?.product_ids?.length > 0 && form.value?.months > 0
  },
]);
const title = computed(() => Translator.actionTitle('promo'));
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value,
    title: steps.value[step.value].title,
    okText: steps.value[step.value].okText,
    cancelText: steps.value[step.value].cancelText,
    disabled: !steps.value[step.value].canNext()
  },
  listeners: {
    close: onCloseOrCancel,
    ok: onOk
  }
}));

const summaryProducts = computed(() => {
  const months = form.value?.months ?? 1;
  const promoProduct = {
    price: promoData.value?.price,
    quantity: months,
    discount: Calculator.getPromoDiscount(promoData.value, months)
  }
  const targetDate = Time.addMonths(Time.now(), months);

  return form.value?.products.map((product) => ({
    product_id: promoProductId,
    orderable_id: product.orderable_id,
    orderable_type: Formatter.model(product.orderable_type),
    name: Translator.t('help.featuring_product', {
      product: product.name,
      date: Formatter.date(targetDate)
    }),
    ...Calculator.getProductTotals(promoProduct),
  }));
})

const onOpenModal = async () => {
  promoProductId = await getPromoProductId();
  showModal.value = true;
  step.value = STEPS.CREATIONS;
}

const onCloseOrCancel = () => {
  if (step.value === STEPS.CREATIONS) {
    return showModal.value = false;
  }

  step.value--;
}

const onOk = () => {
  if (step.value === STEPS.SUMMARY) {
    return validateAndAddToCart();
  }

  step.value++;
}

const validateAndAddToCart = async () => {
  const validated = formElement.value.validate();

  if (! validated) return;

  const accept = await showConfirm(Translator.t('help.you_will_promo_products', {
    total: form.value?.products?.length,
    months: form.value?.months,
  }));

  if (! accept) return;

  await addCartItems(summaryProducts.value, 'promo');

  formElement.value.reset();
  showModal.value = false;
}

const getPromoProductId = async () => {
  if (promoProductId) {
    return promoProductId;
  }

  const { data } = await getJson('products', { params: { product_types: ['promo'] } });

  return data[0];
}
</script>
