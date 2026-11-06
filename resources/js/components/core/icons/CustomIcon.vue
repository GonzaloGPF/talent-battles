<template>
  <svg
    :style="style"
    :viewBox="viewBox"
    :width="width"
    :height="height"
    class="mx-auto"
  >
    <slot>
      <path
        fill="currentColor"
        :d="vector"
      />
    </slot>
  </svg>
</template>
<script setup>
import { computed } from "vue";

const props = defineProps({
    vector: {
        type: String,
        default: null,
    },
    width: {
        type: Number,
        default: 24
    },
    height: {
        type: Number,
        default: 24
    },
    viewBox: {
        type: String,
        default: '0 0 24 24'
    }
});
const style = computed(() => ({
    width: `${ props.width }px`,
    height: `${ props.height }px`,
}))
</script>
