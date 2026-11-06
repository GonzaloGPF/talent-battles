<template>
  <div>
    <v-progress-linear
      v-if="loading"
      indeterminate
    />
    <base-chart
      :chart-data="chartData"
      :title="Translator.t('charts.sales')"
      :sub-title="Translator.t('charts.data_from_last_year')"
      :is-money="true"
      type="bar"
    />
  </div>
</template>
<script setup>
import BaseChart from '@/components/charts/BaseChart.vue';
import Translator from '@/objects/Translator';
import useCharts from '@/composables/useCharts';

const { loading, chartData } = useCharts('stats/creation_sales');
</script>
