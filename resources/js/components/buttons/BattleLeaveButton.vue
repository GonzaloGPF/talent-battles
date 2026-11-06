<template>
  <app-button
    v-if="openBattle"
    :label="Translator.actionTitle('leave', 'battle')"
    :loding="loading"
    color="danger"
    @click="onClick"
  />
</template>
<script setup>
import { computed } from 'vue';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { useConfirm } from '@/stores/confirm';

const emit = defineEmits(['success']);
const props = defineProps({
    model: {
        type: Object,
        required: true,
        default: null
    },
});
const { loading, deleteJson } = useHttp();
const { showConfirm } = useConfirm();
const openBattle = computed(() => props.model?.openBattle);
const onClick = async () => {
    let accept = await showConfirm(Translator.t('help.leave_battle'));

    if (! accept) return;

    deleteJson(`battles/${openBattle.value?.id}`)
        .then(() => emit('success'));
}
</script>
