<template>
  <v-menu
    :close-on-content-click="false"
    offset-y
    top
    open-on-hover
  >
    <template #activator="{ props }">
      <app-button
        v-bind="props"
        :title="Translator.tl('volume')"
        :icon="volumeIcon"
        variant="text"
        size="small"
        @click="muteUnMute"
      />
    </template>
    <v-sheet rounded="xl">
      <v-slider
        :model-value="volume"
        direction="vertical"
        append-icon="mdi-volume-high"
        prepend-icon="mdi-volume-low"
        color="white"
        max="100"
        min="0"
        class="overflow-hidden volume-slider"
        @update:model-value="setVolume"
      />
    </v-sheet>
  </v-menu>
</template>
<script setup>
import Translator from '@/objects/Translator';
import { usePlayer } from '@/stores/player';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';

const { setVolume, muteUnMute } = usePlayer();
const { volume, volumeIcon } = storeToRefs(usePlayer());
</script>
<style>
.volume-slider .v-slider.v-slider--vertical {
    cursor: pointer;
}

.v-menu__content.theme--dark {
    background: rgba(100, 100, 100, 0.6);
}
</style>
