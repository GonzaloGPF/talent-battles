<template>
  <div>
    <slot name="filters" />
    <div
      v-if="items.length"
      class="d-flex justify-end"
    >
      <v-pagination
        v-if="totalPages > 1"
        :model-value="currentPage"
        :length="totalPages"
        :total-visible="visiblePages"
        rounded="circle"
        density="compact"
        @update:model-value="onPage"
      />
    </div>
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <v-row>
      <v-col
        v-for="item in items"
        :key="item.id"
        cols="12"
        md="6"
        lg="4"
        xl="3"
      >
        <slot :item="item" />
      </v-col>
    </v-row>

    <model-gallery
      :models="items"
      :selected-id="selectedId"
      @keydown.esc.stop="$emit('close')"
      @close="$emit('close')"
    />

    <div
      v-if="items.length"
      class="d-flex justify-end"
    >
      <v-pagination
        v-if="totalPages > 1"
        :model-value="currentPage"
        :length="totalPages"
        :total-visible="visiblePages"
        rounded="circle"
        density="compact"
        @update:model-value="onPage"
      />
    </div>
    <app-empty v-else />
  </div>
</template>
<script setup>
import ModelGallery from '@/components/shared/ModelGallery.vue';
import AppEmpty from '@/components/core/AppEmpty.vue';

defineProps({
    items: {
        type: Array,
        required: true,
        default: () => []
    },
    currentPage: {
        type: Number,
        default: 1
    },
    totalPages: {
        type: Number,
        default: null
    },
    pagination: {
        type: Object,
        default: null
    },
    selectedId: {
        type: Number,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    }
});
const emits = defineEmits([
    'page',
    'close',
]);
const visiblePages = 5;
const onPage = (page) => {
    emits('page', page)
}
</script>
