<template>
  <v-container fluid>
    <v-progress-linear
      :active="loading"
      indeterminate
    />

    <report-info :model-value="vModel" />
  </v-container>
</template>
<script setup>
import ReportInfo from '@/components/info/ReportInfo.vue';
import useDetails from '@/composables/useDetails';

const props = defineProps({
    modelId: {
        type: Number,
        default: null
    }
});
const { loading, vModel } = useDetails(props.modelId, 'reports', {
    with: [
        'createdBy',
        'reportable',
        'file',
    ]
});
</script>
