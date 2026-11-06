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
    <template #default="slotProps">
      <user-card
        :user="slotProps.item"
        :actions="UserHelper.actionButtons"
        class="ml-8 mt-8"
        @skill="onSkill"
      />
    </template>
  </index-layout>
</template>
<script setup>
import UserCard from '@/components/cards/UserCard.vue';
import IndexLayout from '@/layouts/IndexLayout.vue';
import userFilters from '@/components/filters/userFilters';
import EventBus from '@/events/EventBus';
import EventTypes from '@/events/EventTypes';
import { ref } from 'vue';
import UserHelper from '@/helpers/UserHelper';
import useFetchItems from '@/composables/useFetchItems';
import useFilterForm from '@/composables/useFilterForm';
import AppFilters from '@/components/core/AppFilters.vue';

const layout = ref(null);
const selectedId = ref(null);
const { filterForm, inputs } = useFilterForm(userFilters);
const { items, currentPage, totalPages, loading, makeRequest } = useFetchItems('users', UserHelper.indexParams, filterForm);

EventBus.on(EventTypes.new_user, makeRequest);

function onSkill(skillId) {
    filterForm.skills = [skillId];
}
</script>
