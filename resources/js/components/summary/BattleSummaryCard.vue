<template>
  <summary-card
    :loading="loading"
    :title="Translator.modelTitle('battle', true)"
    :sub-title="total"
    color="battle"
    icon="$battle"
    :labels="labels"
  />
</template>
<script setup>
import SummaryCard from '@/components/summary/SummaryCard.vue';
import useSummary from '@/composables/useSummary';
import { computed } from 'vue';
import Translator from '@/objects/Translator';

const { loading, total, dataResponse } = useSummary('stats/battles_summary_card');
const labels = computed(() => [
    {
        name: 'open',
        text: Translator.tl('open_battles'),
        value: dataResponse.value?.open,
    },
    {
        name: 'in_progress',
        text: Translator.tl('in_progress'),
        value: dataResponse.value?.in_progress,
    },
    {
        name: 'won',
        text: Translator.tl('won'),
        value: dataResponse.value?.won,
    },
    {
        name: 'lost',
        text: Translator.tl('lost'),
        value: dataResponse.value?.lost,
    },
]);
</script>
