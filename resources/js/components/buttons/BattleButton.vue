<template>
  <AppButton
    :title="title"
    :loading="loading"
    :disabled="isDisabled"
    :modal="modal"
    density="compact"
    icon="$battle"
    size="small"
    variant="text"
    @click="onClick"
  >
    <template #modal>
      <div class="d-flex flex-column justify-center">
        <span
          class="text-center text-subtitle-1 mb-3"
          v-html="challengeText"
        />
        <BattleForm
          v-if="showModal"
          ref="formElement"
          :opponent="opponent"
          @update:selected="selected = $event"
        />
        <span
          class="text-center w-full d-inline-block mx-auto text-caption"
          v-text="textHelp"
        />
      </div>
    </template>
  </AppButton>
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import {computed, defineAsyncComponent, ref, toRef} from 'vue';
import CreationHelper from '@/helpers/CreationHelper';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';

const BattleForm = defineAsyncComponent(() => import('@/components/forms/BattleForm.vue'))

const emit = defineEmits(['success']);
const props = defineProps({
    model: {
        type: Object,
        default: null,
        required: true,
    },
});
const { isLogged, user } = storeToRefs(useAuth());
const { loading, postJson } = useHttp();
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value,
    title: title.value
  },
  listeners: {
    close: () => showModal.value = false,
    ok: makeRequest
  }
}))
const selected = ref(null);
const formElement = ref(null);
const opponent = toRef(props, 'model');
const isTrack = computed(() => CreationHelper.isTrack(opponent.value));
const isDesign = computed(() => CreationHelper.isDesign(opponent.value));
const url = computed(() => {
    const creationId = selected.value?.id;

    if (! creationId) {
        return null;
    }

    if (isTrack.value) {
        return `tracks/${ creationId }/battles`;
    }

    if (isDesign.value) {
        return `designs/${ creationId }/battles`;
    }

    return null;
});
const showModal = ref(false);
const title = computed(() => Translator.modelTitle('battle'));
const isInBattle = computed(() => !! opponent.value?.currentBattle);
const isMine = computed(() => user.value.id === opponent.value?.created_by);
const isDisabled = computed(() =>
    ! isLogged.value
    || isMine.value
    || ! opponent.value?.can_challenge
    || isInBattle.value
);
const challengeText = computed(() => {
    const name = opponent.value?.name;
    const author = opponent.value?.createdBy?.nick_name;

    return Translator.t('help.going_to_challenge', { name, author });
});
const textHelp = computed(() => {
    let type;
    let model;

    if (isTrack.value && opponent.value?.track_type) {
        model = Translator.modelTitle('track');
        type = Translator.tConstName('trackType', opponent.value?.track_type);
    }

    if (isDesign.value && opponent.value?.design_type) {
        model = Translator.modelTitle('design');
        type = Translator.tConstName('designType', opponent.value?.design_type);
    }

    if (! type) return;

    return Translator.t('help.select_your_best', { model, type });
});

function onClick() {
    formElement.value?.reset();
    showModal.value = true;
}

async function makeRequest() {
    const valid = formElement.value?.validate();

    if (! valid || ! url.value) return;

    valid.opponent_id = opponent.value.id;

    const { data } = await postJson(url.value, valid);

    opponent.value.currentBattle = data;
    showModal.value = false;
    emit('success');
}
</script>
