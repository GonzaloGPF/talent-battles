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
      url="albums"
      @view="onView"
      @create="onCreate"
      @edit="onEdit"
      @delete="onDelete"
    >
      <template #default="{ item }">
        <album-details :model-id="item.id" />
      </template>
    </data-table>

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <album-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/albumHeaders';
import AlbumForm from '@/components/forms/AlbumForm.vue';
import filters from '@/components/filters/albumFilters';
import AlbumDetails from '@/components/tables/details/AlbumDetails.vue';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import DataTable from '@/components/tables/DataTable.vue';

defineProps({
    params: {
        type: Object,
        default: () => {
        }
    }
})

const formElement = ref(null);
const tableElement = ref(null);
const {
    title,
    modalTitle,
    showModal,
    loading,
    onView,
    onCreate,
    onEdit,
    onDelete,
    onSubmit,
    onClose,
} = useAdminCrud('album', tableElement, formElement);
</script>
