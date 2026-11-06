<template>
  <index-layout
    ref="layout"
    :items="items"
    :selected-id="selectedId"
    :loading="loading"
    :current-page="currentPage"
    :total-pages="totalPages"
    @close="selectedId = null"
    @page="filterForm.page = $event"
  >
    <template #filters>
      <app-filters
        v-model="filterForm"
        :inputs="inputs"
        @reload="makeRequest(true)"
      />
    </template>
    <template #default="{ item }">
      <track-card
        :track="item"
        :actions="TrackHelper.actionButtons"
        @genre="onGenre"
        @image="selectedId = item.id"
      />
    </template>
  </index-layout>
</template>
<script setup>
import TrackCard from '@/components/cards/TrackCard.vue';
import IndexLayout from '@/layouts/IndexLayout.vue';
import trackFilters from '@/components/filters/trackFilters';
import EventBus from '@/events/EventBus';
import EventTypes from '@/events/EventTypes';
import useFetchItems from '@/composables/useFetchItems';
import { reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import { usePlayer } from '@/stores/player';
import { storeToRefs } from 'pinia';
import TrackHelper from '@/helpers/TrackHelper';
import AppFilters from '@/components/core/AppFilters.vue';
import useFilterForm from '@/composables/useFilterForm';
import CreationHelper from '@/helpers/CreationHelper';

const route = useRoute();
const { initialized } = storeToRefs(usePlayer());
const { init } = usePlayer();
const layout = ref(null);
const selectedId = ref(null);
const baseParams = reactive({
    ...TrackHelper.indexParams,
    track_types: [CreationHelper.routeNameToType(route.name)]
});
const { filterForm, inputs } = useFilterForm(trackFilters);
const { items, currentPage, totalPages, loading, makeRequest } = useFetchItems('tracks', baseParams, filterForm);

watch(items, () => {
    if (! initialized.value && items.value?.length) {
        init(items.value[0]);
    }
});
watch(route, () => {
    baseParams.track_types = [CreationHelper.routeNameToType(route.name)];
});

EventBus.on(EventTypes.new_track, () => makeRequest(true));
EventBus.on(EventTypes.backend['.TrackUpdated'], (model) => {
    if (items.value?.some(item => item.id === model.id)) {
        makeRequest(true);
    }
});

function onGenre(genreId) {
    filterForm.genres = [genreId];
}
</script>
