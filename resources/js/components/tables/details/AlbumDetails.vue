<template>
  <v-container fluid>
    <v-progress-linear
      :active="loading"
      indeterminate
    />

    <album-info :model-value="vModel" with-image />
  </v-container>
</template>
<script setup>
import AlbumInfo from '@/components/info/AlbumInfo.vue';
import AlbumHelper from '@/helpers/AlbumHelper';
import useDetails from '@/composables/useDetails';

const props = defineProps({
    modelId: {
        type: Number,
        default: null
    }
});

const { loading, vModel } = useDetails(props.modelId, 'albums', AlbumHelper.showParams);
</script>
