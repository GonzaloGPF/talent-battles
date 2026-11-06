<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :actions="['delete']"
      :header-actions="[]"
      :params="params"
      :events-reload="[EventTypes.backend.BattleInProgress, EventTypes.backend.BattleFinished]"
      url="battles"
      @delete="onDelete"
    >
      <template #default="{ item }">
        <battle-details :model-id="item.id" />
      </template>
    </data-table>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/battleHeaders';
import filters from '@/components/filters/battleFilters';
import BattleDetails from '@/components/tables/details/BattleDetails.vue';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import DataTable from '@/components/tables/DataTable.vue';
import EventTypes from '@/events/EventTypes';

defineProps({
    params: {
        type: Object,
        default: null
    }
});
const formElement = ref(null);
const tableElement = ref(null);
const {
    title,
    onDelete,
} = useAdminCrud('battles', tableElement, formElement);
</script>
