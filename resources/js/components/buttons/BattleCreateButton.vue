<template>
  <app-button
    v-if="withoutBattles"
    :label="Translator.actionTitle('create', 'battle')"
    :loding="loading"
    :disabled="!model?.can_challenge"
    color="orange"
    prepend-icon="$battle"
    size="small"
    @click="onClick"
  />
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import { computed } from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { useConfirm } from '@/stores/confirm';
import { useFlashMessages } from '@/stores/flashMessages';
import { storeToRefs } from 'pinia';
import { useCompany } from '@/stores/company';
import { useAuth } from '@/stores/auth';

const emit = defineEmits(['success']);
const props = defineProps({
  model: {
    type: Object,
    required: true,
    default: null
  },
});
const { loading, postJson } = useHttp();
const { pushFlashMessage } = useFlashMessages();
const { showConfirm } = useConfirm();
const { createBattlePoints } = storeToRefs(useCompany());
const { user } = storeToRefs(useAuth());

const withoutBattles = computed(() => {
  const openBattle = props.model?.openBattle;
  const currentBattle = props.model?.currentBattle;

  return ! openBattle && ! currentBattle;
});

async function onClick() {
  if (! props.model?.can_challenge) {
    return pushFlashMessage(Translator.t('validation.cannot_challenge', { name: props.model?.name }));
  }

  const accept = await showConfirm(Translator.t('help.create_battle', { points: createBattlePoints.value }));

  if (! accept) return;

  if (user.value?.points < createBattlePoints.value) {
    return pushFlashMessage(Translator.t('validation.not_enough_points'), { type: 'error' });
  }

  postJson('battles', {
    type: props.model?.model,
    id: props.model?.id
  }).then(() => emit('success'));
}
</script>
