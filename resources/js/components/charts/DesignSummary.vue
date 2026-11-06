<template>
  <div>
    <v-progress-linear
      v-if="loading"
      indeterminate
    />
    <base-chart
      :chart-data="chartData"
      :title="Translator.modelTitle('design', true)"
      type="pie"
    />
  </div>
</template>
<script setup>
import BaseChart from '@/components/charts/BaseChart.vue';
import Translator from '@/objects/Translator';
import useCharts from '@/composables/useCharts';

const { loading, chartData } = useCharts('stats/designs_summary');
</script>
