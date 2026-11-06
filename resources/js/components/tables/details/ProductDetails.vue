<template>
  <v-container fluid>
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <div class="max-w-lg">
      <track-info
        v-if="vModel?.orderable?.model === 'track'"
        :model-value="vModel?.orderable"
        class="grey darken-3"
      />
      <design-info
        v-if="vModel?.orderable?.model === 'design'"
        :model-value="vModel?.orderable"
        class="grey darken-3"
      />
    </div>
  </v-container>
</template>
<script setup>
import useDetails from '@/composables/useDetails';
import TrackInfo from '@/components/info/TrackInfo.vue';
import DesignInfo from '@/components/info/DesignInfo.vue';

const props = defineProps({
  modelId: {
    type: Number,
    default: null
  }
});
const { loading, vModel } = useDetails(props.modelId, 'products', {
  with: ['orderable.createdBy', 'file']
});
</script>