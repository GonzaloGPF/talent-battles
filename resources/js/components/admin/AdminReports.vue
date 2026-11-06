<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :actions="getActions"
      :header-actions="[]"
      :params="params"
      :events-reload="[EventTypes.backend.ReportSolved, EventTypes.backend.ReportClosed]"
      url="reports"
      @edit="onCustomEdit"
      @solve="onSolve"
    >
      <template #default="{ item }">
        <report-details :model-id="item.id" />
      </template>
    </data-table>

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <report-form
        ref="formElement"
        :solving="! isEditing"
      />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/reportHeaders';
import filters from '@/components/filters/reportFilters';
import ReportDetails from '@/components/tables/details/ReportDetails.vue';
import ReportForm from '@/components/forms/ReportForm.vue';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import DataTable from '@/components/tables/DataTable.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import EventTypes from '@/events/EventTypes';

defineProps({
  params: {
    type: Object,
    default: null
  }
});
const { isAdmin, user } = storeToRefs(useAuth());
const formElement = ref(null);
const tableElement = ref(null);
const {
  title,
  modalTitle,
  showModal,
  loading,
  update,
  onEdit,
  // onSubmit,
  onClose,
} = useAdminCrud('report', tableElement, formElement);

const isEditing = ref(false);
const report = ref(null);

const onCustomEdit = (item) => {
  onEdit(item);
  report.value = item;
  isEditing.value = true;
}

const onSolve = (item) => {
  onEdit(item);
  report.value = item;
  isEditing.value = false;
}

const onSubmit = () => {
  if (loading.value) return;

  const data = formElement.value?.validate();

  if (! data) {
    return null;
  }

  if (isEditing.value) {
    return update(data);
  } else {
    return update(data, `reports/${report.value.id}/solve`);
  }
}

const getActions = (item) => {
  if (isAdmin.value) {
    return ['edit', 'solve']
  }

  if (item.report_state !== 'open') {
    return [];
  }

  return item.created_by === user.value?.id
    ? ['edit']
    : [];
}
</script>
