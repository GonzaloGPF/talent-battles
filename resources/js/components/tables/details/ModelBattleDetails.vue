<template>
  <v-container fluid>
    <div class="flex space-x-2 mb-2">
      <app-alert
        v-if="model?.currentBattle && isMine"
        :message="Translator.tl('in_battle')"
        variant="tonal"
        density="compact"
        class="max-w-xs"
      />
      <app-alert
        v-if="model?.openBattle && isMine"
        :message="Translator.tl('waiting_opponent')"
        variant="tonal"
        density="compact"
        class="max-w-xs"
      />
    </div>
    <div
      v-if="canSeeButtons"
      class="mb-5"
    >
      <battle-create-button
        v-if="model"
        :model="model"
        @success="$emit('success')"
      />
      <battle-leave-button
        v-if="model"
        :model="model"
        @success="$emit('success')"
      />
    </div>
  </v-container>
</template>
<script setup>
import BattleCreateButton from '@/components/buttons/BattleCreateButton.vue';
import AppAlert from '@/components/core/AppAlert.vue';
import BattleLeaveButton from '@/components/buttons/BattleLeaveButton.vue';
import Translator from '@/objects/Translator';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import { computed } from 'vue';

defineEmits(['success']);
const props = defineProps({
  model: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: null
  },

});
const { user } = storeToRefs(useAuth());
const isMine = computed(() => props.model?.created_by === user.value?.id);
const canSeeButtons = computed(() => isMine.value && ! props.loading);
</script>