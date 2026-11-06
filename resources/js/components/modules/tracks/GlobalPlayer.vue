<template>
  <div
    v-if="track && track.id"
    :class="{ playing }"
    class="global-player d-flex align-center"
  >
    <div class="d-flex justify-center align-center mr-2">
      <app-button
        v-if="mdAndUp"
        icon="mdi-close"
        :title="Translator.actionTitle('close')"
        rounded="pill"
        size="small"
        variant="text"
        @click="closeTrack"
      />
      <app-button
        v-if="mdAndUp"
        :title="Translator.actionTitle('previous')"
        :disabled="!initialized"
        icon="mdi-skip-previous"
        rounded="pill"
        size="small"
        variant="text"
        @click="prev"
      />
      <app-button
        :title="Translator.actionTitle('stop')"
        :disabled="!initialized"
        icon="mdi-stop"
        rounded="pill"
        size="small"
        variant="text"
        @click="stop"
      />
      <app-button
        ref="play"
        :title="playing ? Translator.actionTitle('pause') : Translator.actionTitle('play')"
        :icon="playing ? 'mdi-pause' : 'mdi-play'"
        :color="track.color"
        :disabled="!initialized"
        class="global-play-button"
        rounded="pill"
        variant="text"
        @click="playPause(index)"
      />
      <app-button
        v-if="mdAndUp"
        :disabled="!initialized"
        :title="Translator.actionTitle('next')"
        icon="mdi-skip-next"
        rounded="pill"
        size="small"
        variant="text"
        @click="next"
      />
    </div>
    <span v-text="Formatter.duration(playedSeconds)" />

    <div class="flex-grow-1 mx-2 items-center">
      <v-slider
        v-if="!xs"
        v-width-percentage="onPercentage"
        :model-value="playedSeconds"
        :color="track.color"
        :step=".01"
        :max="track.duration"
        readonly
        class="cursor-pointer global-play-button"
        style="height: 30px"
      />
      <app-button
        v-else
        :label="track.name"
        :route="CreationHelper.getCreationShowRoute(track)"
        size="small"
        class="d-block brand-font mx-auto"
        variant="text"
        style="max-width: 100px"
      />
    </div>

    <span v-text="Formatter.duration(track.duration)" />

    <app-button
      v-if="mdAndUp"
      :title="Translator.actionTitle('repeat')"
      :color="repeat ? 'primary' : null"
      icon="mdi-repeat"
      rounded="pill"
      size="small"
      class="ml-2"
      variant="text"
      @click="toggleRepeat"
    />

    <track-volume class="m-2" />

    <div v-if="!xs">
      <app-button
        :label="track?.createdBy?.nick_name"
        :route="{name: 'users.show', params: {id: track?.created_by}}"
        size="x-small"
        class="d-block"
        color="grey lighten-1"
        variant="text"
      />
      <app-button
        :label="track.name"
        :route="CreationHelper.getCreationShowRoute(track)"
        size="small"
        class="d-block brand-font"
        variant="text"
      />
    </div>
  </div>
</template>
<script setup>
import TrackVolume from '@/components/modules/tracks/TrackVolume.vue';
import CreationHelper from '@/helpers/CreationHelper';
import {default as vWidthPercentage} from '@/directives/widthPercentage';
import {usePlayer} from '@/stores/player';
import {ref, unref, watch} from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import {storeToRefs} from 'pinia';
import Formatter from '@/objects/Formatter';
import {useDisplay} from 'vuetify';

const {
    initialized,
    track,
    playedSeconds,
    repeat,
    playing,
} = storeToRefs(usePlayer());
const {
    closeTrack,
    addTrack,
    playPause,
    stop,
    prev,
    next,
    toggleRepeat,
    setPlayedPercentage,
} = usePlayer();
const index = ref(null);
const { mdAndUp, xs } = useDisplay();
const onPercentage = (percent) => setPlayedPercentage(index.value, percent);

watch(track, addTrackToPlayer);

function addTrackToPlayer() {
    if (! track.value?.id) return;

    index.value = addTrack(unref(track));
}
</script>
<style>
.global-player {
    transition: color .4s ease;
}

.global-player .global-play-button {
    opacity: 0.7;
    transition: opacity .4s ease;
}

.global-player.playing .global-play-button {
    opacity: 1;
}
</style>
