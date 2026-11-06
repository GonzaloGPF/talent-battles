<template>
  <v-switch
    v-model="iValue"
    :name="iName"
    :label="iLabel"
    :hide-details="hideDetails && errors.length === 0"
    :disabled="disabled"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    :error-messages="errors"
    :hint="hint"
    :loading="loading"
    :persistent-hint="true"
    color="primary"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { baseProps } from '@/objects/Props';
import { toRefs, watch } from 'vue';

const props = defineProps({
    ...baseProps,
    loading: {
        type: Boolean,
        default: null
    },
    hideDetails: {
        type: Boolean,
        default: null
    },
});

const { iLabel, iName, iValue } = useInput(toRefs(props));

const emits = defineEmits([
    'update:modelValue',
]);
watch(iValue, (value) => emits('update:modelValue', value));
</script>
