<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :actions="['edit', 'delete']"
      :header-actions="[]"
      :params="tableParams"
      url="favorites"
      @edit="onEdit"
      @delete="onDelete"
    >
      <template #default="{ item }">
        <favorite-details :model-id="item.id" />
      </template>
    </data-table>

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <favorite-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/favoriteHeaders';
import FavoriteForm from '@/components/forms/FavoriteForm.vue';
import filters from '@/components/filters/favoriteFilters';
import FavoriteDetails from '@/components/tables/details/FavoriteDetails.vue';
import useAdminCrud from '@/composables/useAdminCrud';
import { computed, ref, toRef } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import DataTable from '@/components/tables/DataTable.vue';

const props = defineProps({
    params: {
        type: Object,
        default: null
    }
});
const formElement = ref(null);
const tableElement = ref(null);
const propsParams = toRef(props, 'params');
const tableParams = computed(() => ({
    with: [
        'favoriteList',
        'favoritable',
    ],
    ...(propsParams.value || {})
}))
const {
    title,
    modalTitle,
    showModal,
    loading,
    onEdit,
    onDelete,
    onSubmit,
    onClose,
} = useAdminCrud('favorite', tableElement, formElement);
</script>
