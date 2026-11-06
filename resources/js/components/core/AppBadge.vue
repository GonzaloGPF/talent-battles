<template>
  <v-badge
    :bordered="bordered"
    :inline="inline"
    :dot="dot"
    :color="color"
    :icon="icon"
    :floating="floating"
    :content="content"
    :model-value="modelValue"
  >
    <slot />
  </v-badge>
</template>
<script setup>
defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  content: {
    type: [String, Number],
    default: null
  },
  color: {
    type: String,
    default: null
  },
  icon: {
    type: String,
    default: null
  },
  bordered: {
    type: Boolean,
    default: false
  },
  inline: {
    type: Boolean,
    default: false
  },
  dot: {
    type: Boolean,
    default: false
  },
  floating: {
    type: Boolean,
    default: false
  },
});
</script>
