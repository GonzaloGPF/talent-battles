<template>
  <div>
    <data-table
      ref="tableElement"
      :headers="headers"
      :filter-fields="filters"
      :title="title"
      :header-actions="[]"
      :actions="['view', 'edit', 'download', 'delete']"
      :params="params"
      :searchable="true"
      url="products"
      @view="onView"
      @download="onDownload"
      @edit="onEdit"
      @delete="onDelete"
    >
      <template #default="{ item }">
        <product-details :model-id="item.id" />
      </template>
    </data-table>

    <app-modal
      :model-value="showModal"
      :title="modalTitle"
      :loading="loading"
      @ok="onSubmit"
      @close="onClose"
    >
      <product-form ref="formElement" />
    </app-modal>
  </div>
</template>
<script setup>
import headers from '@/components/tables/headers/productHeaders';
import ProductForm from '@/components/forms/ProductForm.vue';
import filters from '@/components/filters/productFilters';
import useAdminCrud from '@/composables/useAdminCrud';
import { ref } from 'vue';
import AppModal from '@/components/core/AppModal.vue';
import DataTable from '@/components/tables/DataTable.vue';
import { useRouter } from 'vue-router';
import CreationHelper from '@/helpers/CreationHelper';
import ProductDetails from '@/components/tables/details/ProductDetails.vue';

defineProps({
  params: {
    type: Object,
    default: null
  }
});
const formElement = ref(null);
const tableElement = ref(null);
const { push } = useRouter();
const {
  title,
  modalTitle,
  showModal,
  loading,
  onEdit,
  onDownload,
  onDelete,
  onSubmit,
  onClose,
} = useAdminCrud('products', tableElement, formElement);

const onView = item => {
  if (['design', 'track'].includes(item.product_type)) {
    return push(CreationHelper.getCreationShowRoute(item.orderable));
  }
}
</script>
