<template>
  <summary-card
    :loading="loading"
    :title="Translator.tl('sales')"
    :sub-title="total"
    color="order"
    icon="$sale"
    :labels="labels"
  />
</template>
<script setup>
import SummaryCard from '@/components/summary/SummaryCard.vue';
import useSummary from '@/composables/useSummary';
import { computed } from 'vue';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

const { loading, total, dataResponse } = useSummary('stats/creation_sales_card');
const labels = computed(() => [
    {
        name: 'total',
        text: Translator.tl('total'),
        value: dataResponse.value?.total,
    },
    {
        name: 'amount',
        text: Translator.tl('amount'),
        value: Formatter.money(dataResponse.value?.amount),
    },
]);
</script>
