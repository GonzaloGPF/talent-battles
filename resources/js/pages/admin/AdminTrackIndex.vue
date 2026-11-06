<template>
  <admin-tracks
    v-if="user"
    :params="params"
  />
</template>
<script setup>
import AdminTracks from '@/components/admin/AdminTracks.vue';
import { useAuth } from '@/stores/auth';
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import TrackHelper from '@/helpers/TrackHelper';

const { user } = storeToRefs(useAuth());

const params = computed(() => {
    const params = { ...TrackHelper.indexParams };

    delete params['waveform'];

    params.created_by = [user.value?.id];

    return params;
});
</script>
