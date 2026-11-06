<template>
  <div>
    <battle-vote class="mx-auto min-h-lg" />

    <v-divider class="my-10" />

    <battle-list class="mt-3" />
  </div>
</template>
<script setup>
import BattleVote from '@/components/battles/BattleVote.vue';
import BattleList from '@/components/lists/BattleList.vue';</script>
