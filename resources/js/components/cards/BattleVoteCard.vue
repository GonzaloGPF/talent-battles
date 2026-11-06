<template>
  <BattleCard
    v-if="battle"
    :battle="battle"
    :loading="loading"
    :disabled="disabled"
    :title="Translator.tl('vote_best')"
    :ready-a="isReadyA"
    :ready-b="isReadyB"
    :timer-a="timerA"
    :timer-b="timerB"
    show-help
    show-vote
    @vote="$emit('vote', $event)"
  />
</template>
<script setup>
import {computed, defineAsyncComponent, ref, toRef, watch} from 'vue';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import { usePlayer } from '@/stores/player';
// import global from '@/config/global';

const REQUIRED_PLAY_SECONDS = 3 // global.isLocal() ? 3 : 10;

defineEmits(['vote']);
const props = defineProps({
  battle: {
    type: Object,
    required: true,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  },
});
const BattleCard = defineAsyncComponent(() => import('@/components/cards/BattleCard.vue'));
const { isLogged } = storeToRefs(useAuth());
const { track, playedSeconds } = storeToRefs(usePlayer());
const timerA = ref(REQUIRED_PLAY_SECONDS);
const timerB = ref(REQUIRED_PLAY_SECONDS);
const isReadyA = ref(false);
const isReadyB = ref(false);
const propBattle = toRef(props, 'battle');

const subjectA = ref(null);
const subjectB = ref(null);
const disabled = computed(() => {
  if (! isLogged.value) return true;

  return ! (isReadyA.value && isReadyB.value);
});

watch(propBattle, () => {
  // If it's not track battle, it will be ready
  isReadyA.value = isReadyB.value = propBattle.value?.battle_type !== 'track';
  timerA.value = timerB.value = REQUIRED_PLAY_SECONDS;

  const items = (propBattle.value?.tracks || propBattle.value?.designs || [null, null]);

  subjectA.value = items[0];
  subjectB.value = items[1];

}, { immediate: true, deep: true });

watch(playedSeconds, () => {
  if (track.value?.id === subjectA.value?.id) {
    timerA.value = REQUIRED_PLAY_SECONDS - Math.floor(playedSeconds.value);
    if (timerA.value <= 0 || track.value?.duration <= REQUIRED_PLAY_SECONDS) {
      timerA.value = 0;
      isReadyA.value = true;
    }
  }

  if (track.value?.id === subjectB.value?.id) {
    timerB.value = REQUIRED_PLAY_SECONDS - Math.floor(playedSeconds.value);
    if (timerB.value <= 0 || track.value?.duration <= REQUIRED_PLAY_SECONDS) {
      timerB.value = 0;
      isReadyB.value = true;
    }
  }
});
</script>
