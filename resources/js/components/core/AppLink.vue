<template>
  <router-link
    :to="route"
    style="color: inherit"
    class="text-decoration-none d-block"
  >
    {{ title }}
  </router-link>
</template>
<script setup>
defineProps({
    title: {
        type: [String, Number],
        default: null
    },
    route: {
        type: Object,
        default: null
    },
})
</script>
