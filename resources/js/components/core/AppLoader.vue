<template>
  <app-icon
    :spin="true"
    :size="props.size"
    icon="sync"
  />
</template>
<script setup>
const props = defineProps({
        size: {
            type: String,
            default: null
        }
    }
);
</script>
