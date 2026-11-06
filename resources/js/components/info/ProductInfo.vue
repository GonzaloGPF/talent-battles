<template>
  <div
    v-if="canShow"
    class="grid grid-cols-3 text-center"
  >
    <app-field
      v-if="! isSold"
      :label="Translator.ta('price')"
      :model-value="model?.price"
      type="money"
    />

    <app-field
      :label="Translator.ta('exclusive')"
      :model-value="model?.exclusive"
      type="boolean"
    />

    <app-field
      v-if="! isSold && model?.last_sale"
      :label="Translator.ta('last_sale')"
      :model-value="model?.last_sale"
      type="date"
    />

    <app-field
      v-if="isSold"
      :title="Translator.tl('sold')"
      :model-value="Translator.tl('sold')"
      color="green"
      type="chip"
    />

    <app-field
      v-if="extended && model?.promoted"
      :label="Translator.ta('promoted')"
      :model-value="model?.promoted"
      :inline="true"
      type="date"
    />

    <app-field
      v-if="extended"
      :label="Translator.ta('product_type')"
      :model-value="Translator.tConstName('product_type', model?.product_type)"
      :inline="true"
      type="date"
    />
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import { computed, toRef } from 'vue';
import AppField from '@/components/core/AppField.vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  },
  extended: {
    type: Boolean,
    default: false,
  }
});
const model = toRef(props, 'modelValue');
const isSold = computed(() => model.value?.exclusive && !! model.value?.last_sale);
const canShow = computed(() => {
  if (model.value?.exclusive && ! model.value?.price && ! model.value?.last_sale) {
    return false
  }
  return model.value?.price || model.value?.exclusive;
});
</script>
