<template>
  <v-footer
    class="border-t"
    style="z-index: 4; transition: height 0.3s ease-in-out"
  >
    <div class="w-full text-center">
      <GlobalPlayer v-if="track && track.id" />
      <div v-if="! track">
        {{ global.getAppName() }} &copy; 2022
      </div>
    </div>
    <FloatingButtons v-if="isLogged" />
  </v-footer>
</template>
<script setup>
import { usePlayer } from '@/stores/player';
import { storeToRefs } from 'pinia';
import global from '@/config/global';
import {defineAsyncComponent} from 'vue'
import {useAuth} from '@/stores/auth'

const GlobalPlayer = defineAsyncComponent(() => import('@/components/modules/tracks/GlobalPlayer.vue'))
const FloatingButtons = defineAsyncComponent(() => import('@/components/core/FloatingButtons.vue'))

const { track } = storeToRefs(usePlayer());
const { isLogged } = storeToRefs(useAuth());
</script>
