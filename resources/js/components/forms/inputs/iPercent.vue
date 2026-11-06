<template>
  <i-number
    v-model="iValue"
    :name="iName"
    :label="iLabel + ' %'"
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
    :hide-details="hideDetails"
    @blur="$emit('blur')"
    @focus="$emit('focus')"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { numberProps } from '@/objects/Props';
import INumber from '@/components/forms/inputs/iNumber.vue';
import { toRefs } from 'vue';

const props = defineProps({
    ...numberProps,
});

const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

defineEmits([
    'input',
    'focus',
    'blur',
    'mousedown',
    'mouseup',
    'clear',
    'append',
    'prepend',
]);

defineExpose({
    reset
});
</script>
