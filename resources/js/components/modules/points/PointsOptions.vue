<template>
  <v-row>
    <v-col
      v-for="(option, i) in [250, 500, 1000]"
      :key="i"
      cols="12"
      :md="4"
    >
      <v-sheet
        elevation="2"
        class="p-2 cursor-pointer"
        rounded
        color="green darken-4"
        @click="$emit('click', option)"
      >
        <app-field
          :label="Translator.tl('points')"
          :model-value="option"
          class="d-block"
          inline
        />
        <app-field
          :label="Translator.tl('discount')"
          :model-value="Calculator.getPointsDiscount(option, discounts)"
          class="d-block"
          inline
          type="percent"
        />
      </v-sheet>
    </v-col>
  </v-row>
</template>
<script setup>
import AppField from '@/components/core/AppField.vue';
import Translator from '@/objects/Translator';
import { useCompany } from '@/stores/company';
import Calculator from '@/objects/Calculator';
import { storeToRefs } from 'pinia';

defineEmits(['click']);
const { discounts } = storeToRefs(useCompany());
</script>
