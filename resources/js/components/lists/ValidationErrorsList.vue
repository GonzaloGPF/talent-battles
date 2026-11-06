<template>
  <v-list
    :items="items"
    density="compact"
    bg-color="transparent"
  />
</template>
<script setup>
import { computed } from 'vue';

const props = defineProps({
    errors: {
        type: Object,
        default: null
    }
})
const items = computed(() => Object
    .keys(props.errors || {})
    .map((errorsKey) => ({
        title: props.errors[errorsKey][0],
        // value: errorsKey
    })))
</script>
