<template>
  <v-color-picker
    v-model="iValue"
    :title="iLabel"
    :label="iLabel"
    :disabled="disabled"
    :hide-canvas="hideCanvas"
    :hide-inputs="hideInputs"
    :hide-mode-switch="hideModeSwitch"
    :show-swatches="showSwatches"
    mode="hexa"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { baseProps } from '@/objects/Props';
import { toRefs, watch } from 'vue';
import global from '@/config/global';

const props = defineProps({
    ...baseProps,
    hideCanvas: {
        type: Boolean,
        default: true
    },
    hideInputs: {
        type: Boolean,
        default: false
    },
    hideModeSwitch: {
        type: Boolean,
        default: true
    },
    showSwatches: {
        type: Boolean,
        default: false
    },
});
const { iLabel, iValue, reset } = useInput(toRefs(props));
const emits = defineEmits(['update:modelValue']);
const DEFAULT_COLOR = global.defaultColor;

if (! iValue.value){
    iValue.value = DEFAULT_COLOR;
}

watch(iValue, (value) => {
    if (! value) return;

    if (typeof value === 'string') {
        return emits('update:modelValue', value);
    }

    return emits('update:modelValue', value.hex.substring(0, 7));
})

defineExpose({
    reset
});
</script>
