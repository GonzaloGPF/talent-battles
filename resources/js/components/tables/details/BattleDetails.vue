<template>
  <v-container fluid>
    <v-progress-linear
      :active="loading"
      indeterminate
    />

    <battle-card :battle="vModel" />
  </v-container>
</template>
<script setup>
import useDetails from '@/composables/useDetails';
import BattleHelper from '@/helpers/BattleHelper';
import BattleCard from '@/components/cards/BattleCard.vue';

const props = defineProps({
    modelId: {
        type: Number,
        default: null
    }
});
const params = {
    ...BattleHelper.indexParams(),
    counts: ['votes']
}
params['with'].push('winner');
params['with'].push('votes');

const { loading, vModel } = useDetails(props.modelId, 'battles', params);
</script>
