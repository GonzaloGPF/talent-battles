<template>
  <div class="data-table">
    <app-filters
      ref="filterElement"
      v-model="filterForm"
      :inputs="inputs"
      @reload="refresh"
    />
    <v-data-table
      ref="tableElement"
      v-model="selected"
      v-model:expanded="expanded"
      :headers="vHeaders"
      :items="items"
      :items-length="totalPages"
      :loading="loading"
      :loading-text="Translator.tl('loading') + '...'"
      :show-select="showSelect"
      :single-select="singleSelect"
      :search="search"
      :dense="dense"
      :items-per-page="15"
      :no-data-text="Translator.tl('empty')"
      class="elevation-1"
      @update:page="filterForm.page = $event"
      @update:sort-by="onSort"
    >
      <template
        v-if="title"
        #top
      >
        <app-toolbar
          :title="title"
          :buttons="headerActions"
          @click="onClick"
        >
          <!--
          <i-text
              v-if="searchable"
              v-model="filterForm.search"
              append-icon="mdi-magnify"
              name="search"
              class="-mb-2"
              clearable
              single-line
              hide-details
          />
          -->
        </app-toolbar>
      </template>

      <template
        v-if="Array.isArray(actions) ? actions.length : actions"
        #[`item.action`]="{ item }"
      >
        <app-buttons
          :buttons="getItemActions(item)"
          @click="onClick($event, item)"
        />
      </template>

      <template
        v-for="(cell, index) in Table.getCustomCells(vHeaders)"
        :key="index"
        #[cell.name]="{ item }"
      >
        <span v-html="Table.getItemContent(cell.header, item)" />
      </template>

      <template #expanded-row="{ item }">
        <td
          :colspan="vHeaders.length"
          class="py-3"
        >
          <slot :item="item" />
        </td>
      </template>

      <template #bottom>
        <div class="text-center pt-2">
          <v-pagination
            v-if="totalPages > 1"
            :model-value="currentPage"
            :length="totalPages"
            density="compact"
            @update:model-value="filterForm.page = $event"
          />
        </div>
      </template>
    </v-data-table>
  </div>
</template>
<script setup>
// import IText from '@/components/forms/inputs/iText.vue';
import {computed, onMounted, ref, toRefs, useSlots, watch} from 'vue';
import Translator from '@/objects/Translator';
import {useAuth} from '@/stores/auth';
import AppToolbar from '@/components/core/AppToolbar.vue';
import AppButtons from '@/components/core/AppButtons.vue';
import AppFilters from '@/components/core/AppFilters.vue';
import EventBus from '@/events/EventBus';
import useFilterForm from '@/composables/useFilterForm';
import {storeToRefs} from 'pinia';
import useFetchItems from '@/composables/useFetchItems';
import Table from '@/objects/Table';

const emits = defineEmits([
  'update:modelValue',
  // 'update:selected',
  'view',
  'create',
  'edit',
  'delete',
  'restore',
  'download',
  'solve'
]);
const props = defineProps({
  headers: {
    type: Array,
    default: () => []
  },
  url: {
    type: String,
    default: null
  },
  title: {
    type: String,
    default: null
  },
  filterFields: {
    type: Array,
    default: () => []
  },
  params: {
    type: Object,
    default: null
  },
  actions: {
    type: [Array, Function],
    default: () => []
  },
  headerActions: {
    type: Array,
    default: () => ['create']
  },
  dense: {
    type: Boolean,
    default: null
  },
  showSelect: {
    type: Boolean,
    default: null
  },
  singleSelect: {
    type: Boolean,
    default: null
  },
  searchable: {
    type: Boolean,
    default: null
  },
  withTrash: {
    type: Boolean,
    default: null
  },
  eventsReload: {
    type: Array,
    default: () => []
  },
  expandable: {
    type: Boolean,
    default: true
  },
  fullObject: {
    type: Boolean,
    default: true
  },
});
const slots = useSlots()
const { isInternal, isAdmin } = storeToRefs(useAuth());
const { url, params, filterFields, withTrash } = toRefs(props);
const { filterForm, inputs } = useFilterForm(filterFields, isInternal, withTrash);
const { items, totalPages, currentPage, loading, makeRequest } = useFetchItems(url, params, filterForm);
const vHeaders = computed(() => {
  return Table
    .addCommonHeaders(props.headers, props.actions.length, !! slots.default && props.expandable, isAdmin.value)
    .map(header => ({
      ...header,
      title: typeof header.title === 'function' ? header.title() : header.title
    }))
})
const filterElement = ref(null);
const search = ref(null);
const selected = ref([]);
const expanded = ref([]);

const onClick = (event, item = null) => emits(event, item);

const onSort = (value) => console.log('onSort!', value);

const refresh = () => makeRequest(true);

const setFilter = (data, overwrite) => filterElement.value?.setForm(data, overwrite);

const getItemActions = (item) => {
  if (typeof props.actions === 'function') {
    return props.actions(item);
  }

  return props.actions;
}

onMounted(() => {
  props.eventsReload.forEach(event => EventBus.on(event, refresh));
});

watch(selected, (value) => {
  if (props.fullObject) {
    return emits('update:modelValue', items.value?.filter(item => value.includes(item.id)));
  }
  return emits('update:modelValue', value);
});

defineExpose({
  refresh,
  setFilter
});
</script>
