<template>
  <div>
    <v-row>
      <v-col :cols="12" :md="6">
        <app-field
          :label="Translator.ta('country_id')"
          :model-value="modelValue?.country?.name || modelValue?.country"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <app-field
          :label="Translator.ta('region_id')"
          :model-value="modelValue?.region?.name || modelValue?.region"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <app-field
          :label="Translator.ta('city')"
          :model-value="modelValue?.city"
        />
      </v-col>
      <v-col :cols="12" :md="6">
        <app-field
          :label="Translator.ta('postcode')"
          :model-value="modelValue?.postcode"
        />
      </v-col>
    </v-row>
    <app-field
      :label="Translator.ta('line1')"
      :model-value="modelValue?.line1"
    />
    <app-field
      :label="Translator.ta('line2')"
      :model-value="modelValue?.line2"
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
    }
});
</script>
