<template>
  <v-sheet
    elevation="5"
    class="p-3 max-w-xs rounded"
    color="blue-darken-4"
  >
    <app-field
      :label="Translator.tl('total_price')"
      :model-value="totalPrice"
      inline
      type="money"
      class="d-block"
    />
    <app-field
      :label="Translator.tl('discount')"
      :model-value="discount"
      inline
      type="percent"
      class="d-block"
    />
    <v-divider />
    <app-field
      :label="Translator.tl('total')"
      :model-value="total"
      inline
      type="money"
      class="d-block"
    />
  </v-sheet>
</template>
<script setup>
import AppField from '@/components/core/AppField.vue';
import { computed } from 'vue';
import Translator from '@/objects/Translator';

const props = defineProps({
    points: {
        type: Number,
        default: 0
    },
    pointPrice: {
        type: Number,
        default: 0
    },
    discount: {
        type: Number,
        default: 0
    },
});
const totalPrice = computed(() => props.points * props.pointPrice);
const total = computed(() => totalPrice.value * (1 - props.discount / 100));
</script>
