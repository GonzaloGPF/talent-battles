<template>
  <div
    v-if="modelValue?.battles?.length"
    class="grid grid-cols-3 text-center mb-2"
  >
    <app-field
      :label="Translator.modelTitle('battle', true)"
      :model-value="modelValue?.battles?.length"
    />

    <app-field
      :label="Translator.tl('victories')"
      :model-value="totalVictories"
    />

    <app-field
      :label="Translator.tl('ratio')"
      :model-value="winRate"
      type="percent"
    />
  </div>
</template>
<script setup>
import Translator from '@/objects/Translator';
import {computed, defineAsyncComponent} from 'vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    }
});
const AppField = defineAsyncComponent(() => import('@/components/core/AppField.vue'))
const totalVictories = computed(() => {
    return (props.modelValue?.battles || [])
        .filter(battle => battle.winner_id === props.modelValue?.id)
        .length;
});
const winRate = computed(() => {
        let totalBattles = (props.modelValue?.battles || []).length;

        if (! totalBattles) return 0;

        return (totalVictories.value / totalBattles) * 100;
    }
);
</script>
