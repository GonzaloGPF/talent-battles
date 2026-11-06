<template>
  <admin-albums
    v-if="user"
    :params="params"
  />
</template>
<script setup>
import AdminAlbums from '@/components/admin/AdminAlbums.vue';
import { computed } from 'vue';
import AlbumHelper from '@/helpers/AlbumHelper';
import { useAuth } from '@/stores/auth';
import { storeToRefs } from 'pinia';

const { user } = storeToRefs(useAuth());
const params = computed(() => ({
    ...AlbumHelper.indexParams,
    created_by: [user.value?.id]
}));
</script>
