<template>
  <i-number
    v-model="iValue"
    :name="iName"
    :label="Formatter.moneySymbol(iLabel)"
    :loading="loading"
    :rules="rules"
    :hint="hint"
    :persistent-hint="persistentHint"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    :errors="errors"
    :clearable="clearable"
    :min="min"
    :max="max"
    :required="required"
    :class="{required}"
    :hide-details="hideDetails"
    type="number"
    @blur="$emit('blur')"
    @focus="$emit('focus')"
    @clear="$emit('clear')"
    @append="$emit('append')"
    @prepend="$emit('prepend')"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { numberProps } from '@/objects/Props';
import INumber from '@/components/forms/inputs/iNumber.vue';
import { toRefs, watch } from 'vue';
import Formatter from '@/objects/Formatter';

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

const props = defineProps({
    ...numberProps,
});
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

watch(iValue, (value) => emits('update:modelValue', value));

defineExpose({
    reset
});
</script>
