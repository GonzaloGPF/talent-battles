<template>
  <v-row>
    <v-col :cols="12" :md="6" :lg="3">
      <app-field
        :label="Translator.ta('email')"
        :model-value="modelValue?.email"
      />
    </v-col>
    <v-col :cols="12" :md="6" :lg="3">
      <app-field
        :label="Translator.tl('details_submitted')"
        :model-value="modelValue?.details_submitted"
        type="boolean"
      />
    </v-col>
    <v-col :cols="12" :md="6" :lg="3">
      <app-field
        :label="Translator.tl('charges_enabled')"
        :model-value="modelValue?.charges_enabled"
        type="boolean"
      />
    </v-col>
    <v-col :cols="12" :md="6" :lg="3">
      <app-field
        :label="Translator.tl('payouts_enabled')"
        :model-value="modelValue?.payouts_enabled"
        type="boolean"
      />
    </v-col>
  </v-row>
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
