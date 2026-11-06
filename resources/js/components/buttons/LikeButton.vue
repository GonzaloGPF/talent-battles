<template>
  <app-button
    :title="Translator.actionTitle('like')"
    :loading="loading"
    :color="isActive ? 'blue' : null"
    :disabled="! isLogged || isMine"
    :label="count || '0'"
    stacked
    density="compact"
    icon="mdi-thumb-up"
    size="small"
    variant="text"
    @click="onClick"
  />
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import { computed, toRef } from 'vue';
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';

const emit = defineEmits(['success']);
const props = defineProps({
    model: {
        type: Object,
        default: null,
        required: true,
    },
});
const { isLogged, user } = storeToRefs(useAuth());
const { loading, postJson, deleteJson } = useHttp();
const vModel = toRef(props, 'model');
const count = computed(() => vModel.value?.likes_count || 0);
const isActive = computed(() => !! vModel.value?.like);
const isMine = computed(() => vModel.value?.created_by === user.value?.id);

function onClick() {
    if (! vModel.value?.id) return;

    if (isActive.value) {
        makeDeleteRequest();
    } else {
        makePostRequest();
    }
}

function makePostRequest(favoriteListId) {
    return postJson('likes', {
        model_type: vModel.value?.model,
        model_id: vModel.value?.id,
        favorite_list_id: favoriteListId
    }).then(onSuccess);
}

function makeDeleteRequest() {
    let id = vModel.value?.like?.id;

    if (! id) return;

    return deleteJson(`likes/${ id }`)
        .then(onSuccess)
}

function onSuccess({ data }) {
    if (data) {
        vModel.value.like = data;
        vModel.value.likes_count++
    } else {
        vModel.value.like = null;
        vModel.value.likes_count--;
    }

    emit('success');
}

// watch(vModel, (value) => {
//     vModel.value = value;
// }, { immediate: true, deep: true });

</script>
