<template>
  <div>
    <app-field
      :label="Translator.ta('price')"
      :model-value="modelValue?.price || 0"
      inline
      type="money"
      class="max-w-xs"
    />
    <app-field
      :label="Translator.ta('quantity')"
      :model-value="modelValue?.quantity || 0"
      inline
      class="d-block"
    />
    <app-field
      :label="Translator.ta('subtotal')"
      :model-value="modelValue?.subtotal"
      inline
      type="money"
      class="d-block"
    />
    <v-divider />
    <app-field
      :label="Translator.ta('discount')"
      :model-value="modelValue?.discount || 0"
      inline
      type="percent"
      class="d-block"
    />
    <app-field
      :label="Translator.ta('discount_amount')"
      :model-value="modelValue?.discount_amount || 0"
      inline
      type="money"
      class="d-block"
    />
    <app-field
      :label="Translator.ta('total')"
      :model-value="modelValue?.total || 0"
      inline
      type="money"
      class="d-block"
    />
    <v-divider />
    <app-field
      :label="Translator.ta('tax')"
      :model-value="modelValue?.tax || 0"
      inline
      type="percent"
      class="d-block"
    />
    <app-field
      :label="Translator.ta('tax_amount')"
      :model-value="modelValue?.tax_amount || 0"
      inline
      type="money"
      class="d-block"
    />
    <app-field
      :label="Translator.ta('total_with_taxes')"
      :model-value="modelValue?.total_with_taxes ||0"
      inline
      type="money"
      class="d-block"
    />
  </div>
</template>
<script setup>
import AppField from '@/components/core/AppField.vue';
import Translator from '@/objects/Translator';

defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
</script>
