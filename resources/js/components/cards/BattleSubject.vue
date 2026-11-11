<template>
  <div
    v-if="subject"
    class="d-flex flex-column"
  >
    <span
      v-if="isFinished"
      class="font-weight-bold text-center d-block mb-3"
      v-text="Translator.tl('value_points', { 'value': points })"
    />
    <TrackCard
      v-if="subject?.model === 'track'"
      :track="subject"
      :highlight-promotion="false"
      class="flex-grow-1"
      @play="$emit('play')"
    />
    <DesignCard
      v-if="subject?.model === 'design'"
      :design="subject"
      :highlight-promotion="false"
      class="flex-grow-1"
    />
    <AppButton
      v-if="! isFinished && showVote"
      v-flash-message="Translator.t('help.points_earned', {points: pointsAwardVote})"
      :label="label"
      :disabled="disabled"
      :loading="loading"
      :class="{'shine-me': ready}"
      size="large"
      class="mt-4 mx-auto"
      @click="$emit('vote', subject)"
    />
    <AppIcon
      v-if="isFinished"
      :title="Translator.tl('winner')"
      :class="{ invisible: ! isWinner }"
      color="green"
      icon="$medal"
      size="large"
      class="mx-auto"
    />
  </div>
  <AppEmpty
    v-else
    :label="Translator.t('help.waiting_opponent')"
    class="d-flex justify-center align-center rounded-lg border-1 border-solid"
  />
</template>
<script setup>
import Translator from '@/objects/Translator';
import { default as vFlashMessage } from '@/directives/flashMessage';
import { useCompany } from '@/stores/company';
import { storeToRefs } from 'pinia';
import {computed, defineAsyncComponent} from 'vue';

defineEmits(['play', 'vote']);
const props = defineProps({
  subject: {
    type: Object,
    default: null
  },
  points: {
    type: Number,
    default: 0
  },
  timer: {
    type: Number,
    default: 0
  },
  isWinner: {
    type: Boolean,
    default: false
  },
  isFinished: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  ready: {
    type: Boolean,
    default: false
  },
  showVote: {
    type: Boolean,
    default: false
  },
});
const TrackCard = defineAsyncComponent(() => import('@/components/cards/TrackCard.vue'));
const DesignCard = defineAsyncComponent(() => import('@/components/cards/DesignCard.vue'));
const AppButton = defineAsyncComponent(() => import('@/components/core/AppButton.vue'));
const AppIcon = defineAsyncComponent(() => import('@/components/core/AppIcon.vue'));
const AppEmpty = defineAsyncComponent(() => import('@/components/core/AppEmpty.vue'));
const { pointsAwardVote } = storeToRefs(useCompany());
const label = computed(() => {
  let text = Translator.actionTitle('vote');

  if (! props.ready) {
    text += ` (${ props.timer })`;
  }

  return text;
})
</script>
