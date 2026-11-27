<template>
  <div>
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <h3
      class="text-h6 brand-font text-center"
      v-text="Translator.tl('weekly_activity')"
    />
    <div class="flex flex-col md:items-center">
      <app-alert
        :message="Translator.t('help.weekly_activity')"
        class="my-4 md:my-8 md:max-w-2xl"
      />
      <simple-table
        :items="weeks"
        :headers="headers"
      />
    </div>
  </div>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import AppAlert from '@/components/core/AppAlert.vue';
import SimpleTable from '@/components/tables/SimpleTable.vue';
import headers from '@/components/tables/headers/weeklyActivityHeaders';

const { getJson, loading } = useHttp();
const weeks = ref([]);

onMounted(async () => {
    const response = await getJson('stats/weekly_activity');

    weeks.value = response?.data?.weeks || [];
});
</script>
