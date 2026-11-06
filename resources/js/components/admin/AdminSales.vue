<template>
  <div>
    <data-table
      ref="table"
      :headers="headers"
      :filter-fields="filters"
      :title="Translator.modelTitle('sale', true)"
      :actions="[]"
      :header-actions="[]"
      :events-reload="[EventTypes.backend.TrackSold, EventTypes.backend.DesignSold]"
      url="sales"
    />
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/saleHeaders';
import filters from '@/components/filters/saleFilters';
import EventTypes from '@/events/EventTypes';
import DataTable from '@/components/tables/DataTable.vue';
import Translator from '@/objects/Translator';</script>
