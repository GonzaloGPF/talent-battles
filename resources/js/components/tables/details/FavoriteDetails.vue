<template>
  <v-container fluid>
    <app-alert
      v-if="!vModel?.favoritable && !loading"
      :message="Translator.t('help.content_has_been_deleted')"
      border="start"
      variant="tonal"
      class="max-w-md"
    />
    <track-details
      v-if="favoritableModel === 'track'"
      :model-id="vModel?.favoritable.id"
    />
    <design-details
      v-if="favoritableModel === 'design'"
      :model-id="vModel?.favoritable.id"
    />
    <album-details
      v-if="favoritableModel === 'album'"
      :model-id="vModel?.favoritable.id"
    />
    <user-details
      v-if="favoritableModel === 'user'"
      :model-id="vModel?.favoritable.id"
    />
  </v-container>
</template>
<script setup>
import TrackDetails from '@/components/tables/details/TrackDetails.vue';
import DesignDetails from '@/components/tables/details/DesignDetails.vue';
import UserDetails from '@/components/tables/details/UserDetails.vue';
import AlbumDetails from '@/components/tables/details/AlbumDetails.vue';
import useDetails from '@//composables/useDetails';
import { computed } from 'vue';
import AppAlert from '@/components/core/AppAlert.vue';
import Translator from '@/objects/Translator';

const props = defineProps({
    modelId: {
        type: Number,
        default: null
    }
});
const { loading, vModel } = useDetails(props.modelId, 'favorites', {
    with: [
        'favoriteList',
        'favoritable',
    ],
});
const favoritableModel = computed(() => vModel?.value?.favoritable?.model);
</script>
