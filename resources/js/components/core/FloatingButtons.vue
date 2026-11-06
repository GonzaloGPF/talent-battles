<template>
  <div class="absolute right-0 mb-48">
    <div class="relative d-flex flex-column mr-8">
      <UploadButton class="mb-3" />
      <OpenBattleButton />
    </div>
  </div>
</template>
<script setup>
import {defineAsyncComponent} from 'vue'

const UploadButton = defineAsyncComponent(() => import('@/components/buttons/UploadButton.vue'));
const OpenBattleButton = defineAsyncComponent(() => import('@/components/buttons/OpenBattleButton.vue'));
</script>
