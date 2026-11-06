<template>
  <div>
    <v-row class="mt-10">
      <v-col :cols="12" :md="6" :lg="3">
        <track-summary-card />
      </v-col>
      <v-col :cols="12" :md="6" :lg="3">
        <design-summary-card />
      </v-col>
      <v-col :cols="12" :md="6" :lg="3">
        <battle-summary-card />
      </v-col>
      <v-col :cols="12" :md="6" :lg="3">
        <activity-summary-card />
      </v-col>
    </v-row>
    <v-row class="mt-10">
      <v-col :cols="12" :md="6" :lg="4">
        <track-summary />
      </v-col>
      <v-col :cols="12" :md="6" :lg="4">
        <design-summary />
      </v-col>
      <v-col :cols="12" :md="6" :lg="4">
        <battle-summary />
      </v-col>
    </v-row>
    <div class="md:p-6 mt-16">
      <activity-received-line
        class="w-full -mt-10"
        style="height: 400px"
      />
    </div>
  </div>
</template>
<script setup>
import TrackSummary from '@/components/charts/TrackSummary.vue';
import ActivityReceivedLine from '@/components/charts/ActivityReceivedLine.vue';
import BattleSummary from '@/components/charts/BattleSummary.vue';
import TrackSummaryCard from '@/components/summary/TrackSummaryCard.vue';
import BattleSummaryCard from '@/components/summary/BattleSummaryCard.vue';
import ActivitySummaryCard from '@/components/summary/ActivitySummaryCard.vue';
import DesignSummaryCard from '@/components/summary/DesignSummaryCard.vue';
import DesignSummary from '@/components/charts/DesignSummary.vue';</script>
