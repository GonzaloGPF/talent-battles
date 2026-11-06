<template>
  <router-view v-slot="{ Component }" class="w-100">
    <transition
      mode="out-in"
      enter-active-class="animate__faster animate__fadeIn"
      leave-active-class="animate__faster animate__fadeOut"
    >
      <component :is="Component" />
    </transition>
  </router-view>
</template>
<script setup>
</script>