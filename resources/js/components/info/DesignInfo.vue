<template>
  <div class="d-flex flex-column fill-height">
    <div class="d-flex justify-space-between">
      <app-field
        :title="Translator.ta('name')"
        :model-value="modelValue?.createdBy?.nick_name"
        :route-data="{name: 'users.show', params: {id: modelValue?.created_by}}"
        icon="mdi-account"
        type="route"
        class="brand-font"
      />
      <app-field
        :title="Translator.ta('created_by')"
        :model-value="modelValue?.created_at"
        :inline="true"
        type="date"
      />
    </div>

    <div class="d-flex justify-space-between mb-2">
      <app-field
        :title="Translator.ta('name')"
        :model-value="modelValue?.name"
        :route-data="CreationHelper.getCreationShowRoute(modelValue)"
        :icon="CreationHelper.getCreationIcon(modelValue)"
        type="route"
      />
      <app-field
        v-if="canShowAlbum"
        :title="Translator.ta('album_id')"
        :model-value="Formatter.text(modelValue?.album?.name, 25)"
        :route-data="{name: 'albums.show', params: {id: modelValue?.album_id}}"
        icon="$album"
        type="route"
      />
    </div>

    <div class="grid grid-cols-3 gap-y-2 text-center mb-2">
      <app-field
        v-if="modelValue?.design_type"
        :label="Translator.ta('design_type')"
        :model-value="Translator.tConstName('designType', modelValue?.design_type)"
      />
      <app-field
        v-if="modelValue?.style"
        :model-value="Translator.tConstName('style', modelValue?.style)"
        type="chip"
      />
      <app-field
        v-if="modelValue?.aspect_ratio"
        :label="Translator.ta('aspect_ratio')"
        :model-value="Translator.tConstName('aspectRatio', modelValue?.aspect_ratio)"
      />
    </div>

    <product-info
      :model-value="modelValue?.product"
      class="mb-2"
    />

    <battle-data-info
      :model-value="modelValue"
      class="mb-2"
    />

    <app-field
      v-if="modelValue?.description"
      :label="Translator.ta('description')"
      :model-value="modelValue?.description"
      class="flex-grow-1"
    />
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import { computed } from 'vue';
import ProductInfo from '@/components/info/ProductInfo.vue';
import BattleDataInfo from '@/components/info/BattleDataInfo.vue';
import AppField from '@/components/core/AppField.vue';
import CreationHelper from '@/helpers/CreationHelper';
import Formatter from '@/objects/Formatter';

const props = defineProps({
  modelValue: {
    type: Object,
    default: null
  }
});
const canShowAlbum = computed(() =>
  props.modelValue?.album?.name && props.modelValue?.album_id
);
</script>
