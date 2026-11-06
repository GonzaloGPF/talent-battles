<template>
  <admin-invitations
    v-if="user"
    :params="{ created_by: [user?.id], with: ['user']}"
  />
</template>
<script setup>
import AdminInvitations from '@/components/admin/AdminInvitations.vue';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';

const { user } = storeToRefs(useAuth());
</script>
