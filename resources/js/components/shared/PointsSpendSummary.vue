<template>
  <text-sheet
    :title="title || Translator.tl('summary')"
    color="blue-darken-4"
  >
    <app-field
      :label="Translator.tl('your_balance')"
      :model-value="getValuePointsText(points)"
      :inline="true"
    />
    <div v-if="price">
      <app-field
        :label="Translator.ta('price')"
        :model-value="'-' + getValuePointsText(price)"
        :inline="true"
      />
      <v-divider />
      <app-field
        :label="Translator.tl('after_purchase')"
        :model-value="getValuePointsText(remainingPoints)"
        :inline="true"
        :error="! canAfford"
        :success="canAfford"
      />
      <span
        v-if="remainingPoints < 0"
        class="text-caption text-red d-block mt-2"
        v-text="Translator.t('validation.not_enough_points')"
      />
    </div>
  </text-sheet>
</template>
<script setup>
import TextSheet from '@/components/shared/TextSheet.vue';
import Translator from '@/objects/Translator';
import { computed } from 'vue';
import { useAuth } from '@/stores/auth';
import AppField from '@/components/core/AppField.vue';
import { storeToRefs } from 'pinia';

const props = defineProps({
    price: {
        type: Number,
        default: 0
    },
    title: {
        type: String,
        default: null
    },
});
const { user } = storeToRefs(useAuth());

const points = computed(() => user.value?.points || 0);
const remainingPoints = computed(() => points.value - props.price);
const canAfford = computed(() => remainingPoints.value > 0);

const getValuePointsText = (value) => Translator.tl('value_points', { value });
</script>
