<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :actions="['edit', 'delete']"
      :params="params"
      url="favorite_lists"
      @create="onCreate"
      @edit="onEdit"
      @delete="onDelete"
    />

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <favorite-list-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/favoriteListHeaders';
import FavoriteListForm from '@/components/forms/FavoriteListForm.vue';
import filters from '@/components/filters/favoriteListFilters';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import DataTable from '@/components/tables/DataTable.vue';

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
} = useAdminCrud('favoriteList', tableElement, formElement);
</script>
