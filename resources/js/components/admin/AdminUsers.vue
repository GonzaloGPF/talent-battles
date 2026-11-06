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
      :events-reload="[EventTypes.backend['.UserUpdated']]"
      url="users"
      @view="onView"
      @create="onCreate"
      @edit="onEdit"
      @delete="onDelete"
    >
      <template #default="{ item }">
        <user-details :model-id="item.id" />
      </template>
    </data-table>

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <user-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/userHeaders';
import filters from '@/components/filters/userFilters';
import UserForm from '@/components/forms/UserForm.vue';
import UserDetails from '@/components/tables/details/UserDetails.vue';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
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
    onCreate,
    onEdit,
    onDelete,
    onSubmit,
    onClose,
    onView,
} = useAdminCrud('user', tableElement, formElement);
</script>
