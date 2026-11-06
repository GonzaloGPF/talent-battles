<template>
  <v-file-input
    ref="fileElement"
    :name="iName"
    :label="iLabel"
    :rules="rules"
    :accept="accept"
    :show-size="showSize"
    :multiple="multiple"
    :hint="hint"
    :persistent-hint="persistentHint"
    :prepend-icon="prependIcon"
    :dense="dense"
    :outlined="outlined"
    :chips="chips"
    :small-chips="smallChips"
    :counter="counter"
    :clearable="clearable"
    :disabled="disabled"
    :error-messages="errors"
    :hide-details="hideDetails"
    truncate-length="15"
    @update:model-value="iValue = $event"
  />
</template>
<script setup>
import {ref, toRefs, watch} from 'vue';
import useInput from '@/composables/useInput';
import {baseProps} from '@/objects/Props';

const emits = defineEmits(['update:modelValue']);
const props = defineProps({
    ...baseProps,
    showSize: {
        type: Boolean,
        default: false
    },
    accept: {
        type: String,
        default: null
    },
    multiple: {
        type: Boolean,
        default: false
    },
    chips: {
        type: Boolean,
        default: false
    },
    smallChips: {
        type: Boolean,
        default: false
    },
    counter: {
        type: Boolean,
        default: false
    },
    clearable: {
        type: Boolean,
        default: null
    },
});
const { iLabel, iName } = useInput(toRefs(props));
const iValue = ref();
const fileElement = ref(null);
const click = () => {
    const input = fileElement.value?.$el.querySelector('input');

    if (!input) return;

    return input.click();
}
const reset = () => {
    fileElement.value?.reset();
    iValue.value = props.multiple
        ? []
        : null
}
watch(iValue, () => {
    emits('update:modelValue', iValue.value)
});

defineExpose({
    reset,
    click
});
</script>
