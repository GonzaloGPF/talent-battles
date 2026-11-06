<template>
  <BaseCard
    :actions="actions"
    :chips="album?.tags"
    :model="album"
    :image-style="{'height': '400px'}"
    class="base-card rounded-xl"
    @chip="$emit('tag', $event.id)"
    @action="onAction"
  >
    <template #title>
      <div class="d-flex justify-space-between align-center w-100">
        <app-field
          v-if="album?.created_by"
          :model-value="album?.createdBy?.nick_name"
          :route-data="{name: 'users.show', params: {id: album?.created_by}}"
          size="small"
          color="grey lighten-1"
        />
        <app-field
          v-if="album?.id"
          :model-value="album?.name"
          size="small"
          class="brand-font"
        />
      </div>
    </template>

    <template #info>
      <AlbumInfo
        v-if="album?.id"
        :model-value="album"
      />
    </template>
  </BaseCard>
</template>
<script setup>
import {defineAsyncComponent} from 'vue'

defineEmits(['tag', 'image']);
defineProps({
    album: {
        type: Object,
        required: true,
        default: null
    },
    actions: {
        type: Array,
        default: () => []
    },
});
const AlbumInfo = defineAsyncComponent(() => import('@/components/info/AlbumInfo.vue'));
const BaseCard = defineAsyncComponent(() => import('@/components/cards/BaseCard.vue'));
const AppField = defineAsyncComponent(() => import('@/components/core/AppField.vue'));
// const avatarUrl = computed(() => Utils.getFileURL(props.album?.avatar));
const onAction = () => {
};
</script>
