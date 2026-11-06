<template>
  <admin-battles
    v-if="user"
    :params="params"
  />
</template>
<script setup>
import AdminBattles from '@/components/admin/AdminBattles.vue';
import { useAuth } from '@/stores/auth';
import { computed } from 'vue';
import { storeToRefs } from 'pinia';

const { user } = storeToRefs(useAuth());
const params = computed(() => ({
        with: [
            'winner',
            'votes',
        ],
        counts: [
            'votes',
        ],
        by_participants: [user.value?.id]
    }
));
</script>
