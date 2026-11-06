<template>
  <app-button
    :title="Translator.actionTitle('favorite')"
    :loading="loading"
    :color="isActive ? 'pink' : null"
    :disabled="! isLogged || isMine"
    :label="count || '0'"
    density="compact"
    stacked
    icon="mdi-heart"
    size="small"
    variant="text"
    @click="onClick"
  >
    <template #menu>
      <v-menu
        activator="parent"
        open-on-hover
        top
        offset-y
        :disabled="isActive"
      >
        <favorite-lists-list
          :favorite-lists="favoriteLists"
          @click="makePostRequest"
        />
      </v-menu>
    </template>
  </app-button>
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import { computed, ref, watch } from 'vue';
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import FavoriteListsList from '@/components/lists/FavoriteListsList.vue';

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
const vModel = ref({});
const count = computed(() => vModel.value?.favorites_count || 0);
const favoriteLists = computed(() => user.value?.favoriteLists || []);
const isActive = computed(() => !! vModel.value?.favorite);
const isMine = computed(() => vModel.value?.created_by === user.value.id);

function onClick() {
    if (! vModel.value?.id) return;

    if (isActive.value) {
        makeDeleteRequest();
    } else {
        makePostRequest();
    }
}

function makePostRequest(favoriteListId){
    postJson('favorites', {
            model_type: vModel.value?.model,
            model_id: vModel.value?.id,
            favorite_list_id: favoriteListId
        },
    ).then(onSuccess);
}

function makeDeleteRequest() {
    let id = vModel.value?.favorite?.id;

    if (! id) return;

    return deleteJson(`favorites/${ id }`)
        .then(onSuccess)
}

function onSuccess({ data }){
    if (data) {
        vModel.value.favorite = data;
        vModel.value.favorites_count++;
    } else {
        vModel.value.favorite = null;
        vModel.value.favorites_count--;
    }

    emit('success');
}

watch(props.model, (value) => {
    vModel.value = value;
}, { immediate: true, deep: true });
</script>
