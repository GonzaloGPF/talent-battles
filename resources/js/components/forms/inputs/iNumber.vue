<template>
  <i-text
    ref="v-input"
    v-model="iValue"
    :label="iLabel"
    :name="iName"
    type="number"
    :min="min"
    :max="max"
    :loading="loading"
    :disabled="disabled"
    :rules="rules"
    :hint="hint"
    :filled="filled"
    :clearable="clearable"
    :single-line="singleLine"
    :persistent-hint="persistentHint"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    :errors="errors"
    :required="required"
    :class="{required}"
    :readonly="readonly"
    :hide-details="props.hideDetails"
    @append="$emit('append')"
    @prepend="$emit('prepend')"
    @clear="$emit('clear')"
    @focus="$emit('focus')"
    @blur="$emit('blur')"
    @mousedown="$emit('mousedown')"
    @mouseup="$emit('mouseup')"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { numberProps } from '@/objects/Props';
import IText from '@/components/forms/inputs/iText.vue';
import { toRefs, watch } from 'vue';

const props = defineProps({
    ...numberProps,
});
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

const emits = defineEmits([
    'update:modelValue',
    'focus',
    'blur',
    'mousedown',
    'mouseup',
    'clear',
    'append',
    'prepend',
]);

watch(iValue, (value) => emits('update:modelValue', value));

defineExpose({
    reset
})
</script>
