<template>
  <v-container fluid>
    <div v-if="user?.is_account_ready">
      <v-row class="mb-10">
        <v-col :cols="12" :md="6" :lg="3">
          <creation-sale-summary-card />
        </v-col>
      </v-row>

      <div class="p-6 grey darken-4 rounded">
        <creation-sales-bar class="w-full grey darken-4 -mt-10 rounded elevation-4" />
      </div>
    </div>
    <account-alerts
      v-else
      class="max-w-lg"
    />
  </v-container>
</template>
<script setup>
import CreationSalesBar from '@/components/charts/CreationSalesBar.vue';
import CreationSaleSummaryCard from '@/components/summary/CreationSaleSummaryCard.vue';
import { useAuth } from '@/stores/auth'
import { storeToRefs } from 'pinia';
import AccountAlerts from '@/components/alerts/AccountAlerts.vue';

const { user } = storeToRefs(useAuth());
</script>
