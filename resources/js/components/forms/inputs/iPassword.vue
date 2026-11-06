<template>
  <i-text
    v-model="iValue"
    :append-icon="show ? 'mdi-eye' : 'mdi-eye-off'"
    :name="iName"
    :label="iLabel"
    :loading="loading"
    :disabled="disabled"
    :rules="rules"
    :hint="hint"
    :persistent-hint="persistentHint"
    :prepend-icon="prependIcon ? prependIcon : '$password'"
    :errors="errors"
    :clearable="clearable"
    :class="{required}"
    :required="required"
    :hide-details="hideDetails"
    :type="show ? 'text' : 'password'"
    @append="show = !show"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { textProps } from '@/objects/Props';
import IText from '@/components/forms/inputs/iText.vue';
import { ref, toRefs, watch } from 'vue';

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
    ...textProps
});

const show = ref(false);

const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

watch(iValue, (value) => emits('update:modelValue', value));

defineExpose({
    reset
});
</script>
