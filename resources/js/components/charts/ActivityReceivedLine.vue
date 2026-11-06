<template>
  <div>
    <v-progress-linear
      v-if="loading"
      indeterminate
    />
    <base-chart
      :chart-data="chartData"
      :title="Translator.t('charts.activity_received')"
      :sub-title="Translator.t('charts.data_from_last_year')"
      type="line"
    />
  </div>
</template>
<script setup>
import BaseChart from '@/components/charts/BaseChart.vue';
import Translator from '@/objects/Translator';
import useCharts from '@/composables/useCharts';

const { loading, chartData } = useCharts('stats/activity_received');
</script>
