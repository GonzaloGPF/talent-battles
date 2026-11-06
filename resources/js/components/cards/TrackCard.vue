<template>
  <BaseCard
    :model="track"
    :actions="actions"
    :chips="trackGenres"
    :image-style="{ height: '200px' }"
    :style="{ 'min-height': actions.length ? '400px' : '300px' }"
    class="base-card rounded-xl"
    @chip="$emit('genre', $event.value)"
    @action="onAction"
    @image="$emit('image', imageUrl)"
  >
    <template #title>
      <div class="d-flex flex-col md:flex-row justify-space-between align-center w-100">
        <app-field
          v-if="track?.created_by"
          :model-value="track?.createdBy?.nick_name"
          :route-data="{name: 'users.show', params: { id: track?.created_by }}"
          size="small"
          color="grey lighten-1"
        />
        <app-field
          v-if="track?.name"
          :model-value="track?.name"
          :route-data="TrackHelper.showRoute(track)"
          size="small"
          class="brand-font"
        />
      </div>
    </template>

    <TrackPlayer
      v-if="track?.id"
      ref="playerElement"
      :track="track"
      @play="$emit('play')"
    />

    <template #info>
      <TrackInfo
        v-if="track?.id"
        :model-value="track"
      />
    </template>
  </BaseCard>
</template>
<script setup>
import {computed, defineAsyncComponent, ref} from "vue";
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import TrackHelper from '@/helpers/TrackHelper';

defineEmits(['genre', 'image', 'play']);
const props = defineProps({
    track: {
        type: Object,
        required: true,
        default: null
    },
    actions: {
        type: Array,
        default: () => []
    },
});
const BaseCard = defineAsyncComponent(() => import('@/components/cards/BaseCard.vue'));
const TrackPlayer = defineAsyncComponent(() => import('@/components/modules/tracks/TrackPlayer.vue'));
const AppField = defineAsyncComponent(() => import('@/components/core/AppField.vue'));
const TrackInfo = defineAsyncComponent(() => import('@/components/info/TrackInfo.vue'));
const playerElement = ref(null);
// const audioUrl = computed(() => getFileURL(props.track.audio));
const imageUrl = computed(() => Utils.getFileURL(props.track?.image));
const trackGenres = computed(() => props.track?.genres?.map(genre => ({
    name: 'genre',
    value: genre,
    label: Translator.tConstName('genre', genre),
})).filter(v => v.label));
const onAction = () => {
};
</script>
