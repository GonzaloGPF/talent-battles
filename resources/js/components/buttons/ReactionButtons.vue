<template>
  <div class="d-flex flex-column flex-lg-row justify-space-between w-full text-white border-t pt-2">
    <div class="d-flex justify-space-around text-center align-center interaction-buttons mb-2 mb-xl-0">
      <app-button
        v-if="actions.includes(buttons.info.title)"
        :title="Translator.tl(buttons.info.title)"
        :color="open ? 'primary' : null"
        :icon="buttons.info.icon"
        density="compact"
        size="small"
        variant="text"
        @click="$emit(buttons.info.event, open)"
      />
      <share-button
        v-if="actions.includes('share')"
        :model="model"
      />
      <buy-creation-button
        v-if="actions.includes('buy')"
        :creation="model"
      />
      <battle-button
        v-if="actions.includes('battle')"
        :model="model"
      />
      <report-button
        v-if="! itsMeOrMine"
        :model="model"
      />
      <app-button
        v-if="actions.includes(buttons.delete.title)"
        :title="Translator.tl(buttons.delete.title)"
        :icon="buttons.delete.icon"
        color="red"
        density="compact"
        size="small"
        variant="text"
        @click="$emit(buttons.delete.event, open)"
      />
    </div>
    <div
      v-if="model"
      class="d-flex justify-space-around text-center align-center"
    >
      <like-button
        v-if="actions.includes('like')"
        :model="model"
      />
      <favorite-button
        v-if="actions.includes('favorite')"
        :model="model"
      />
      <comment-button
        v-if="actions.includes('comment')"
        :model="model"
      />
      <download-button
        v-if="actions.includes('download')"
        :model="model"
      />
    </div>
  </div>
</template>
<script setup>
import { useAuth } from '@/stores/auth';
import {computed, defineAsyncComponent} from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import buttons from '@/config/buttons';

defineEmits(['info', 'delete']);
const props = defineProps({
  model: {
    type: Object,
    required: true,
    default: null
  },
  actions: {
    type: Array,
    default: () => []
  },
  open: {
    type: Boolean,
    default: false
  },
});
const LikeButton = defineAsyncComponent(() => import('@/components/buttons/LikeButton.vue'));
const FavoriteButton = defineAsyncComponent(() => import('@/components/buttons/FavoriteButton.vue'));
const CommentButton = defineAsyncComponent(() => import('@/components/buttons/CommentButton.vue'));
const DownloadButton = defineAsyncComponent(() => import('@/components/buttons/DownloadButton.vue'));
const BuyCreationButton = defineAsyncComponent(() => import('@/components/buttons/BuyCreationButton.vue'));
const BattleButton = defineAsyncComponent(() => import('@/components/buttons/BattleButton.vue'));
const ShareButton = defineAsyncComponent(() => import('@/components/buttons/ShareButton.vue'));
const ReportButton = defineAsyncComponent(() => import('@/components/buttons/ReportButton.vue'));
const { user } = storeToRefs(useAuth());
const itsMeOrMine = computed(() => {
  if (props.model.model === 'user') {
    return props.model.id === user.value?.id;
  }
  return props.model.created_by === user.value?.id;
});
</script>
<style>
.interaction-buttons button ~ button {
  margin-inline-start: .15em !important;
}
</style>
