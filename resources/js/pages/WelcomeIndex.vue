<template>
  <div>
    <div
      class="d-flex flex-column fill-height justify-center align-center text-white mb-10"
    >
      <AppLogo />
    </div>
    <v-container style="min-height: 600px">
      <BattleList :params="{ limit: 6 }" />
    </v-container>
  </div>
</template>
<script setup>
import BattleList from '@/components/lists/BattleList.vue';
import AppLogo from '@/components/core/AppLogo.vue'
</script>
