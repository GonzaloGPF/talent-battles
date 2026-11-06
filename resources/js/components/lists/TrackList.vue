<template>
  <v-row v-if="tracks.length">
    <v-col
      v-for="track in tracks"
      :key="track.id"
      :cols="12"
      :lg="6"
      :xl="4"
    >
      <track-card
        :track="track"
        :actions="TrackHelper.actionButtons"
      />
    </v-col>
  </v-row>
  <app-empty
    v-else
    model="track"
  />
</template>
<script setup>
import TrackCard from '@/components/cards/TrackCard.vue';
import AppEmpty from '@/components/core/AppEmpty.vue';
import TrackHelper from '@/helpers/TrackHelper';

defineProps({
    tracks: {
        type: Array,
        default: () => []
    },
});
</script>
