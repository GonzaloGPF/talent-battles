<template>
  <v-text-field
    v-model="iValue"
    :label="iLabel"
    :name="iName"
    :type="type"
    :loading="loading"
    :disabled="disabled"
    :rules="rules"
    :hint="hint"
    :placeholder="iLabel"
    :filled="filled"
    :clearable="clearable"
    :single-line="singleLine"
    :persistent-hint="persistentHint"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    :error-messages="errors"
    :autocomplete="autocomplete ? 'new-password' : null"
    :required="required"
    :class="{required}"
    :readonly="readonly"
    :hide-details="hideDetails"
    @focus="$emit('focus')"
    @blur="$emit('blur')"
    @mouseover.stop="$emit('mouseover', $event)"
    @mouseenter.stop="$emit('mouseenter', $event)"
    @mouseleave.stop="$emit('mouseleave', $event)"
    @mousedown="$emit('mousedown')"
    @mouseup="$emit('mouseup')"
    @click:append="clearable ? $emit('clear') : $emit('append')"
    @click:prepend="$emit('prepend')"
    @click:clear="onClear"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { textProps } from '@/objects/Props';
import { toRefs, watch } from 'vue';

const props = defineProps({
  ...textProps
});

const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

const emits = defineEmits([
  'update:modelValue',
  'focus',
  'blur',
  'mousedown',
  'mouseup',
  'mouseover',
  'mouseenter',
  'mouseleave',
  'clear',
  'append',
  'prepend',
]);

watch(iValue, (value) => emits('update:modelValue', value));

const onClear = () => {
  emits('clear');
  reset();
}

defineExpose({
  reset
});
</script>
