<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :actions="['view', 'edit', 'delete']"
      :params="params"
      :searchable="true"
      :events-reload="[EventTypes.backend.TrackSold]"
      url="tracks"
      @view="$router.push(TrackHelper.showRoute($event))"
      @create="onCreate"
      @edit="onEdit"
      @delete="onDelete"
    >
      <template #default="{ item }">
        <track-details
          :model-id="item.id"
          @success="refresh"
        />
      </template>
    </data-table>

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <track-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/trackHeaders';
import TrackForm from '@/components/forms/TrackForm.vue';
import filters from '@/components/filters/trackFilters';
import TrackDetails from '@/components/tables/details/TrackDetails.vue';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import TrackHelper from '@/helpers/TrackHelper';
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
  modalTitle,
  showModal,
  loading,
  refresh,
  onCreate,
  onEdit,
  onDelete,
  onSubmit,
  onClose,
} = useAdminCrud('tracks', tableElement, formElement);
</script>
