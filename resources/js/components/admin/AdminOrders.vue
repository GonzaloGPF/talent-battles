<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="Translator.modelTitle('order', true)"
      :actions="[]"
      :header-actions="[]"
      :params="tableParams"
      :events-reload="[EventTypes.backend.OrderSuccess]"
      url="orders"
    >
      <template #default="{ item }">
        <order-details
          :model-id="item.id"
          @success="refresh"
        />
      </template>
    </data-table>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/orderHeaders';
import filters from '@/components/filters/orderFilters';
import OrderDetails from '@/components/tables/details/OrderDetails.vue';
import EventTypes from '@/events/EventTypes';
import { computed, ref } from 'vue';
import DataTable from '@/components/tables/DataTable.vue';
import Translator from '@/objects/Translator';

const props = defineProps({
    params: {
        type: Object,
        default: null
    }
});
const tableElement = ref();
const tableParams = computed(() => ({
    with: ['createdBy'],
    counts: ['orderLines'],
    ...props.params
}));
const refresh = () => tableElement.value?.refresh();
</script>
