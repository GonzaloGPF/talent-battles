<template>
  <div v-html="modelValue" />
</template>
<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: null
    }
})
</script>
