<template>
  <div>
    <v-progress-circular
      :rotate="-90"
      :width="3"
      :model-value="modelValue"
      :color="vColor"
      :title="vTitle"
      class="caption rounded-circle black"
    >
      {{ modelValue }}
    </v-progress-circular>
  </div>
</template>
<script setup>
import { computed } from 'vue';
import CreationHelper from '@/helpers/CreationHelper';
import Translator from '@/objects/Translator';

const props = defineProps({
    modelValue: {
        type: Number,
        default: 0
    },
    title: {
        type: String,
        default: null
    },
    color: {
        type: String,
        default: null
    },
});
const vColor = computed(() => {
    if (props.color) return props.color;

    return CreationHelper.ratingColor(props.modelValue);
});
const vTitle = computed(() => {
    if (props.title) return props.title;

    return Translator.ta('rating');
});
</script>
