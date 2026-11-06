<template>
  <admin-orders
    v-if="user"
    :params="{ created_by: [user?.id]}"
  />
</template>
<script setup>
import AdminOrders from '@/components/admin/AdminOrders.vue';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';

const { user } = storeToRefs(useAuth());
</script>
