<template>
  <v-sheet
    :color="color"
    elevation="2"
    class="p-6 rounded-lg"
  >
    <slot name="title">
      <h3
        class="text-overline"
        v-text="title"
      />
    </slot>
    <slot>
      <div v-if="text">
        <app-quote
          v-if="quote"
          :text="text"
        />
        <app-text
          v-else
          :model-value="text"
          class="text-body-1"
        />
      </div>
    </slot>
  </v-sheet>
</template>
<script setup>
import AppText from '@/components/core/AppText.vue';
import AppQuote from '@/components/core/AppQuote.vue';

defineProps({
    title: {
        type: String,
        default: null
    },
    text: {
        type: String,
        default: null
    },
    quote: {
        type: Boolean,
        default: null
    },
    color: {
        type: String,
        default: null
    }
});
</script>
