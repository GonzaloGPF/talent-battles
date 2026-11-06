<template>
  <summary-card
    :loading="loading"
    :title="Translator.tl('sales')"
    :sub-title="total"
    color="track"
    icon="$track"
    :labels="labels"
  />
</template>
<script setup>
import SummaryCard from '@/components/summary/SummaryCard.vue';
import useSummary from '@/composables/useSummary';
import { computed } from 'vue';
import Translator from '@/objects/Translator';

const { loading, total, dataResponse } = useSummary('stats/creations_summary_card');
const labels = computed(() =>
    Object.keys(dataResponse.value || {})
        .map(key => ({
            name: key,
            text: Translator.tConstName('trackType', key),
            value: dataResponse[key]
        }))
        .slice(0, 4)
);
</script>
