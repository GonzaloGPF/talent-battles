<template>
  <app-button
    v-if="canShow"
    :label="title"
    :disabled="isDisabled"
    :loading="loading"
    prepend-icon="mdi-currency-usd"
    color="yellow"
    class="black--text"
    size="small"
    @click="onClick"
  />
</template>
<script setup>
import { computed } from 'vue';
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';

const emits = defineEmits(['success']);
const props = defineProps({
    order: {
        type: Object,
        default: null
    }
});
const { user } = storeToRefs(useAuth());
const { getJson, loading } = useHttp();
const isMine = computed(() => user.value?.id === props.order?.created_by);
const isDisabled = computed(() => ! isMine.value);
const canShow = computed(() => isMine.value && props.order?.payment_state === 'pending');
const title = computed(() => Translator.actionTitle('complete'));

async function onClick() {
    await getJson(`orders/${props.order?.id}/complete`);

    emits('success');
}
</script>
