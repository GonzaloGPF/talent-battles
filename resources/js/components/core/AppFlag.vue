<template>
  <span
    class="fi"
    :class="vClass"
    :style="sizes[props.size || 'small']"
  />
</template>
<script setup>
import { computed } from 'vue';

const props = defineProps({
    icon: {
        type: String,
        default: null
    },
    size: {
        type: String,
        default: undefined,
        validator: value => ['x-small', 'small', 'large', 'x-large'].includes(value)
    },
});
const vClass = computed(() => `fi-${props.icon?.toLowerCase()}`);
const sizes = {
    'x-small': { width: '15px', height: '15px' },
    'small': { width: '20px', height: '20px' },
    'large': { width: '50px', height: '50px' },
    'x-large': { width: '100px', height: '100px' },
}
</script>
