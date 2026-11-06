<template>
  <div>
    <app-field
      :label="Translator.ta('country_id')"
      :model-value="modelValue?.country_code"
    />
    <app-field
      :label="Translator.ta('line1')"
      :model-value="modelValue?.line1"
    />
    <app-field
      :label="Translator.ta('line2')"
      :model-value="modelValue?.line2"
    />
    <app-field
      :label="Translator.ta('postcode')"
      :model-value="modelValue?.postcode"
    />
    <app-field
      :label="Translator.ta('city')"
      :model-value="modelValue?.city"
    />
    <app-field
      :label="Translator.ta('region_id')"
      :model-value="modelValue?.region_code"
    />
    <app-field
      v-if="phone"
      :label="Translator.ta('phone')"
      :model-value="phone"
    />
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppField from '@/components/core/AppField.vue';

defineProps({
    modelValue: {
        type: Object,
        default: null
    },
    phone: {
        type: String,
        default: null
    }
});
</script>
