<template>
  <app-button
    v-if="isLogged"
    :label="Translator.tl('points')"
    :modal="modal"
    variant="text"
    size="small"
    @click="showModal = true"
  >
    <template #modal>
      <docs-button
        path="points"
        class="float-right"
      />
      <app-alert
        :message="Translator.t('help.buy_points_alert')"
        variant="tonal"
        class="mb-3"
        border="start"
      />
      <div
        class="d-flex flex-column justify-center align-center w-100"
        style="min-width: 300px"
      >
        <buy-points-form
          ref="formElement"
          v-model="form"
          class="w-100"
        />
        <!--
        <v-sheet
          v-if="form.points"
          elevation="5"
          class="p-3 max-w-xs rounded animate__animated animate__fadeIn"
          color="blue-darken-4"
        >
          <cart-item-info
            :model-value="product"
            class="w-100"
          />
        </v-sheet>
        -->
      </div>
    </template>
  </app-button>
</template>
<script setup>
import BuyPointsForm from '@/components/forms/BuyPointsForm.vue';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import { computed, ref } from 'vue';
import AppButton from '@/components/core/AppButton.vue';
import AppAlert from '@/components/core/AppAlert.vue';
import { storeToRefs } from 'pinia';
import { useCart } from '@/stores/cart';
import { useCompany } from '@/stores/company';
import Calculator from '@/objects/Calculator';
// import CartItemInfo from '@/components/info/CartItemInfo.vue';
import DocsButton from '@/components/buttons/DocsButton.vue';
import useHttp from '@/composables/useHttp';

const { isLogged, tax } = storeToRefs(useAuth());
const { addCartItem } = useCart();
const { getJson, loading: httpLoading } = useHttp();
const { loading } = storeToRefs(useCart());
const { pointPrice, discounts } = storeToRefs(useCompany());
const showModal = ref(false);
const form = ref({
  points: 0,
});
const formElement = ref(null);
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    title: title.value,
    loading: loading.value || httpLoading.value,
    okText: Translator.t('help.add_to_cart')
  },
  listeners: {
    close: () => showModal.value = false,
    ok: addToCart,
  }
}));
const title = computed(() => Translator.actionTitle('buy') + ' ' + Translator.tl('points'));
const product = computed(() => {
  const points = form.value.points || 0;
  const discount = Calculator.getPointsDiscount(points, discounts.value);

  return {
    name: title.value,
    description: Translator.t('help.buying_points', { points }),
    ...Calculator.getProductTotals(pointPrice.value, points, discount, tax.value)
  }
});

const addToCart = async () => {
  const data = formElement.value?.validate();

  if (! data) return;

  const pointProduct = await getPointsProduct();

  await addCartItem({
    ...product.value,
    product_id: pointProduct.id,
  }, 'points');

  formElement.value.reset();
  showModal.value = false;
}

const toggleModal = () => {
  showModal.value = ! showModal.value;
}

const getPointsProduct = async () => {
  const { data } = await getJson('products', { params: { product_types: ['points'] } });

  return data[0];
}

defineExpose({
  toggleModal
});
</script>
