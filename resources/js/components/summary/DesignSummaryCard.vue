<template>
  <summary-card
    :loading="loading"
    :title="Translator.modelTitle('design', true)"
    :sub-title="subTitle"
    color="design"
    icon="$design"
    :labels="labels"
  />
</template>
<script setup>
import SummaryCard from '@/components/summary/SummaryCard.vue';
import useSummary from '@/composables/useSummary';
import { computed } from 'vue';
import Translator from '@/objects/Translator';

const { loading, dataResponse } = useSummary('stats/designs_summary_card');
const subTitle = computed(() =>
    Object.keys(dataResponse.value || {})
        .map(key => dataResponse.value[key])
        .reduce((value, result) => value + result, 0)
);
const labels = computed(() =>
    Object.keys(dataResponse.value || {})
        .map(key => ({
            name: key,
            text: key !== 'none' ? Translator.tConstName('designType', key) : Translator.t('exceptions.without_data', { data: Translator.ta('type') }),
            value: dataResponse.value[key]
        }))
        .slice(0, 4))
</script>
