<template>
  <div>
    <v-progress-linear
      v-if="loading"
      indeterminate
    />
    <base-chart
      :chart-data="chartData"
      :title="Translator.t('charts.battles')"
      type="pie"
    />
  </div>
</template>
<script setup>
import BaseChart from '@/components/charts/BaseChart.vue';
import Translator from '@/objects/Translator';
import useCharts from '@/composables/useCharts';

const { loading, chartData } = useCharts('stats/battles_summary');
</script>
