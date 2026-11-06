<template>
  <div>
    <account-alerts
      v-if="! user.is_account_ready"
      class="mb-2 max-w-lg"
    />
    <admin-sales :params="{ created_by: [user?.id]}" />
  </div>
</template>
<script setup>
import AdminSales from '@/components/admin/AdminSales.vue';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';
import AccountAlerts from '@/components/alerts/AccountAlerts.vue';

const { user } = storeToRefs(useAuth());
</script>
