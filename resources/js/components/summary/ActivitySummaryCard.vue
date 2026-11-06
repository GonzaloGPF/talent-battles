<template>
  <summary-card
    :loading="loading"
    :title="Translator.tl('community')"
    :sub-title="total"
    color="activity"
    icon="$activity"
    :labels="labels"
  />
</template>
<script setup>
import SummaryCard from '@/components/summary/SummaryCard.vue';
import useSummary from '@/composables/useSummary';
import { computed } from 'vue';
import Translator from '@/objects/Translator';

const { loading, total, dataResponse } = useSummary('stats/activity_summary_card');
const labels = computed(() => [
    {
        name: 'comments',
        text: Translator.tl('comments'),
        value: dataResponse.value?.comments,
    },
    {
        name: 'likes',
        text: Translator.tl('likes'),
        value: dataResponse.value?.likes,
    },
    {
        name: 'favorites',
        text: Translator.tl('favorites'),
        value: dataResponse.value?.favorites,
    },
    {
        name: 'downloads',
        text: Translator.tl('downloads'),
        value: dataResponse.value?.downloads,
    },
]);
</script>
