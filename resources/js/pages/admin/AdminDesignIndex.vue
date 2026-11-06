<template>
  <admin-designs
    v-if="user"
    :params="params"
  />
</template>
<script setup>
import AdminDesigns from '@/components/admin/AdminDesigns.vue';
import { useAuth } from '@/stores/auth';
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import DesignHelper from '@/helpers/DesignHelper';

const { user } = storeToRefs(useAuth());
const params = computed(() => ({
    ...DesignHelper.indexParams,
    created_by: [user.value?.id]
}));
</script>
