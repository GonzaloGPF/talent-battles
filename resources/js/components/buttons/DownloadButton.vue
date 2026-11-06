<template>
  <app-button
    :title="Translator.actionTitle('download')"
    :loading="loading"
    :color="isActive ? 'primary' : null"
    :disabled="isDisabled"
    :label="count || '0'"
    stacked
    density="compact"
    icon="mdi-download"
    size="small"
    variant="text"
    @click="onClick"
  />
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import { computed, ref, watch } from 'vue';
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
const { loading, postJson, downloadFile } = useHttp();
const { isLogged } = storeToRefs(useAuth());
const vModel = ref({});
const count = computed(() => vModel.value?.downloads_count || 0)
const isActive = computed(() => !! vModel.value?.download);
const file = computed(() => vModel.value?.audio || vModel.value?.image || vModel.value?.file);
const isDisabled = computed(() =>
    ! vModel.value?.id
    || ! file.value
    || ! isLogged.value
);

function onClick() {
    if (isDisabled.value) return;

    return postJson('downloads', {
        model_type: vModel.value.model,
        model_id: vModel.value.id,
        file_id: file.value.id
    }, { quietly: true })
        .then(onSuccess)
}

function onSuccess(response) {
    vModel.value.download = response.data
    // this.$set(this.vModel, 'download', data);

    if (response.created) {
        vModel.value.downloads_count++;
    }

    emit('success');

    downloadFile(file.value);
}

watch(props.model, (value) => {
    vModel.value = value;
}, { immediate: true, deep: true });
</script>
