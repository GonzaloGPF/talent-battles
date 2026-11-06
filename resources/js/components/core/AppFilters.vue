<template>
  <div v-if="inputs && inputs.length">
    <div class="d-flex items-center justify-between flex-row align-center mb-4">
      <app-buttons
        :buttons="buttons"
        variant="text"
        class="mr-1"
        size="small"
        @click="onClick"
      />

      <span
        :style="{visibility: filterText ? 'visible' : 'hidden'}"
        class="text-xs flex-grow-1 ml-2"
        v-text="Translator.tl('filtering_by', {value: filterText})"
      />

      <app-button
        :title="Translator.actionTitle('refresh')"
        variant="text"
        icon="mdi-refresh"
        class="mr-5"
        size="small"
        @click="onClick('refresh')"
      />
    </div>

    <filter-form
      v-if="show"
      ref="formElement"
      v-model="form"
      :inputs="inputs"
      @keyup.esc="onErase"
      @filter-text="filterText"
    />
  </div>
</template>
<script setup>
import FilterForm from '@/components/forms/FilterForm.vue';
import Translator from '@/objects/Translator';
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import AppButtons from '@/components/core/AppButtons.vue';
import AppButton from '@/components/core/AppButton.vue';
import { onBeforeRouteLeave, useRouter } from 'vue-router';
import Utils from '@/objects/Utils';

const emits = defineEmits([
    'update:modelValue',
    'reload',
]);
const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    },
    inputs: {
        type: Array,
        default: () => []
    },
    downloadUrl: {
        type: String,
        default: null
    },
    withTrash: {
        type: Boolean,
        default: false,
    },
    params: {
        type: Object,
        default: null
    }
});
const { replace } = useRouter();
const form = ref(props.modelValue);
const formElement = ref(null);
const show = ref(false);
const formReady = ref(false);
const filterText = ref('');
const buttons = computed(() => {
    const buttons = ['filter', 'erase'];

    if (props.downloadUrl) {
        buttons.splice(1, 0, 'download');
    }

    return buttons;
});

const onClick = (event) => {
    switch (event) {
        case 'download':
            // return download(`/export/${props.downloadUrl}`, this.validateParams());
            break;
        case 'erase':
            return onErase();
        case 'refresh':
            return emits('reload');
        case 'filter':
            return show.value = ! show.value;
    }
}

const onErase = () => {
    if (formElement.value) {
        formElement.value.reset()
    } else {
        Object.keys(form.value)
            .forEach(key => form.value[key] = null);
    }
}

function updateQuery() {
    const query = Utils.removeEmpty(form.value);

    replace({ query });
}

function setForm(data, overwrite) {
    if (overwrite) {
        form.value = data;
    } else {
        form.value = {
            ...form.value,
            ...(data || {})
        }
    }
}

watch(form, () => {
    if (formReady.value) {
        updateQuery();
    }
}, { deep: true, immediate: true });

onMounted(() => nextTick(() => formReady.value = true));

onBeforeRouteLeave(() => {
    formReady.value = false;
    onErase();
    nextTick(() => formReady.value = true);
});

defineExpose({
    setForm
});
</script>
