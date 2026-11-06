<template>
  <summary-card
    ref="card"
    :loading="loading"
    :title="Translator.modelTitle('track', true)"
    :sub-title="subTitle"
    :labels="labels"
    color="track"
    icon="$track"
  />
</template>
<script setup>
import SummaryCard from '@/components/summary/SummaryCard.vue';
import useSummary from '@/composables/useSummary';
import { computed } from 'vue';
import Translator from '@/objects/Translator';

const { loading, dataResponse } = useSummary('stats/tracks_summary_card');
const subTitle = computed(() =>
    Object.keys(dataResponse.value || {})
        .map(key => dataResponse.value[key])
        .reduce((value, result) => value + result, 0));
const labels = computed(() =>
    Object.keys(dataResponse.value || {})
        .map(key => ({
            name: key,
            text: Translator.tConstName('trackType', key),
            value: dataResponse.value[key]
        }))
        .slice(0, 3));
</script>
