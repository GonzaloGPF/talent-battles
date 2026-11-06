<template>
  <blockquote
    :title="text"
    v-text="Formatter.text(text, limit)"
  />
</template>
<script setup>
import Formatter from '@/objects/Formatter';

defineProps({
    text: {
        type: String,
        default: null,
        required: true
    },
    limit: {
        type: Number,
        default: 30
    },
});
</script>
