<template>
  <app-icon
    v-if="modelValue"
    icon="mdi-check"
  />
  <span
    v-else
    v-text="Translator.tl('no')"
  />
</template>
<script setup>
import AppIcon from '@/components/core/AppIcon.vue';
import Translator from '@/objects/Translator';

defineProps({
    modelValue: {
        type: Boolean,
        default: false
    }
})
</script>
