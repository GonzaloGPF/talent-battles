<template>
  <v-progress-linear
    :active="loading"
    :indeterminate="indeterminate"
  />
</template>
<script setup>
defineProps({
  loading: Boolean,
  indeterminate: Boolean
});
</script>
