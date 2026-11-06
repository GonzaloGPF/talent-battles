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
    <v-row justify="center">
      <v-col :cols="12" :md="6">
        <div
          v-if="vModel?.id"
          style="max-width: 350px"
        >
          <design-card
            :design="vModel"
            class="grey darken-3"
          />
        </div>
      </v-col>
      <v-col :cols="12" :md="6">
        <design-info
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
import DesignInfo from '@/components/info/DesignInfo.vue';
import DesignCard from '@/components/cards/DesignCard.vue';
import useDetails from '@/composables/useDetails';
import DesignHelper from '@/helpers/DesignHelper';
import ModelBattleDetails from '@/components/tables/details/ModelBattleDetails.vue';
import ModelProductDetails from '@/components/tables/details/ModelProductDetails.vue';

defineEmits(['success']);
const props = defineProps({
    modelId: {
        type: Number,
        default: null
    }
});
const { loading, vModel, refresh } = useDetails(props.modelId, 'designs', DesignHelper.showParams);
</script>
