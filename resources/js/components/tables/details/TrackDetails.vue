<template>
  <v-container fluid>
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <model-battle-details
      :model="vModel"
      :loading="loading"
      @success="refresh"
    />

    <v-row justify="center" class="w-100">
      <v-col :cols="12" :md="6">
        <div
          v-if="vModel && vModel.id"
          style="max-width: 350px"
        >
          <track-card
            :track="vModel"
            class="grey darken-3"
          />
        </div>
      </v-col>
      <v-col :cols="12" :md="6">
        <track-info
          :model-value="vModel"
          style="max-width: 350px"
        />
      </v-col>
    </v-row>

    <model-product-details
      v-if="vModel"
      :model="vModel"
      class="mt-3"
      @success="refresh"
    />
  </v-container>
</template>
<script setup>
import TrackCard from '@/components/cards/TrackCard.vue';
import TrackInfo from '@/components/info/TrackInfo.vue';
import useDetails from '@/composables/useDetails';
import TrackHelper from '@/helpers/TrackHelper';
import ModelBattleDetails from '@/components/tables/details/ModelBattleDetails.vue';
import ModelProductDetails from '@/components/tables/details/ModelProductDetails.vue';

defineEmits(['success']);
const props = defineProps({
    modelId: {
        type: Number,
        default: null
    }
});
const { loading, vModel, refresh } = useDetails(props.modelId, 'tracks', TrackHelper.showParams);
</script>
