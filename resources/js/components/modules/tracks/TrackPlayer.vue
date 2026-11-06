<template>
  <div
    v-if="propsTrack?.id"
    :class="{ playing: isPlaying }"
    class="track-player"
  >
    <div class="d-flex justify-center align-center">
      <span v-text="formattedCurrentTime" />
      <div class="mx-xl-2">
        <app-button
          ref="play"
          :title="isPlaying ? Translator.actionTitle('pause') : Translator.actionTitle('play')"
          :icon="isPlaying ? 'mdi-pause' : 'mdi-play'"
          :color="propsTrack?.color ?? DEFAULT_COLOR"
          :disabled="!initialized"
          class="track-play-button"
          variant="text"
          size="large"
          rounded="pill"
          @click="onPlayPause"
        />
      </div>
      <span v-text="Formatter.duration(propsTrack?.duration)" />
    </div>
    <div
      v-if="propsTrack?.waveform"
      ref="waveformElement"
      v-width-percentage="onPercentage"
      v-resize="drawWaveform"
      class="track-player-progress-container w-full cursor-pointer d-flex align-center animate__animated animate__fadeIn animate__slower"
    >
      <div
        class="track-player-progress"
        :style="{ width: `${ width }px` }"
      />
    </div>
    <div v-else class="flex flex-column justify-center align-center p-2">
      <app-icon size="x-large" icon="mdi-waveform" class="animate__animated animate__rubberBand animate__infinite animate__delay" />
      <span class="text-caption text-grey" v-text="Translator.tl('generating_waveform')+'...'" />
    </div>
  </div>
</template>
<script setup>
import WaveDrawer from '@/objects/WaveDrawer';
import { default as vWidthPercentage } from '@/directives/widthPercentage';
import { usePlayer } from '@/stores/player';
import { computed, nextTick, ref, toRef, watch } from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import Formatter from '@/objects/Formatter';
import AppIcon from '@/components/core/AppIcon.vue';
import global from '@/config/global';

const emits = defineEmits(['play']);
const props = defineProps({
    track: {
        type: Object,
        default: null
    },
});
const {
    initialized,
    track: trackPlayer,
    playing,
    playedSeconds,
    progressPercent,
} = storeToRefs(usePlayer());
const {
    addTrack,
    playPause,
    setPlayedPercentage
} = usePlayer();
const DEFAULT_COLOR = global.defaultColor;
const index = ref(null);
const width = ref(0);
const waveformElement = ref(null);
const propsTrack = toRef(props, 'track');
const isActive = computed(() => propsTrack.value?.id === trackPlayer.value?.id);
const isPlaying = computed(() => isActive.value && playing.value);
const formattedCurrentTime = computed(() => isActive.value ? Formatter.duration(playedSeconds.value) : '00:00');

const addTrackToPlayer = () => {
    if (! propsTrack.value) return;
    index.value = addTrack(propsTrack.value);
}

const onPlayPause = () => {
    emits('play');
    playPause(index.value);
}
// const onStop = () => stop();
const onPercentage = (percent) => setPlayedPercentage(index.value, percent);
const drawWaveform = () => {
    const json = propsTrack.value?.waveform;

    if (! json) return;

    nextTick(() => WaveDrawer.draw(json, waveformElement.value, propsTrack.value.color ?? DEFAULT_COLOR));
}

const setWidth = () => {
    if (isActive.value && waveformElement.value) {
        width.value = progressPercent.value * waveformElement.value.offsetWidth;
    } else {
        width.value = 0;
    }
}

watch(propsTrack, () => {
    addTrackToPlayer();
    setWidth();
    setTimeout(drawWaveform, 100);
}, { immediate: true, deep: true });

watch(progressPercent, () => {
    if (! waveformElement.value) return;

    setWidth();
});
</script>
<style>
.track-player {
    transition: color .4s ease;
}

.track-player .waveform {
    fill-opacity: 0.7;
    transition: fill-opacity .4s ease;
}

.track-player.playing .waveform {
    fill-opacity: 1;
}

.track-player .track-play-button {
    opacity: 0.7;
    transition: opacity .4s ease;
}

.track-player.playing .track-play-button {
    opacity: 1;
}

.track-player-progress-container {
    position: relative;
    height: 60px;
}

.track-player .track-player-progress {
    height: 60px;
    background: rgba(0, 0, 0, 0.1);
    position: absolute;
    transition: all .20s;
}
</style>
