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
      <design-card
        :design="item"
        :actions="DesignHelper.actionButtons"
        @thematic="onThematic"
        @style="onStyle"
        @image="selectedId = item.id"
      />
    </template>
  </index-layout>
</template>
<script setup>
import IndexLayout from '@/layouts/IndexLayout.vue';
import designFilters from '@/components/filters/designFilters';
import EventBus from '@/events/EventBus';
import DesignCard from '@/components/cards/DesignCard.vue';
import EventTypes from '@/events/EventTypes';
import useFetchItems from '@/composables/useFetchItems';
import { ref } from 'vue';
import DesignHelper from '@/helpers/DesignHelper';
import AppFilters from '@/components/core/AppFilters.vue';
import useFilterForm from '@/composables/useFilterForm';

const layout = ref(null);
const selectedId = ref(null);
const { filterForm, inputs } = useFilterForm(designFilters);
const { items, currentPage, totalPages, loading, makeRequest } = useFetchItems('designs', DesignHelper.indexParams, filterForm);

EventBus.on(EventTypes.new_design, () => makeRequest(true));
EventBus.on(EventTypes.backend['.DesignUpdated'], (model) => {
    if (items.some(item => item.id === model.id)) {
        makeRequest(true);
    }
});

function onThematic(thematicId) {
    filterForm.thematics = [thematicId];
}

function onStyle(styleId) {
    filterForm.styles = [styleId];
}
</script>
