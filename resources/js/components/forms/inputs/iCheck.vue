<template>
  <v-checkbox
    v-model="iValue"
    :name="iName"
    :label="iLabel"
    :disabled="disabled"
    :error-messages="errors"
    :required="required"
    :class="{required}"
    :indeterminate="indeterminate"
    :hide-details="hideDetails"
    :hint="hint"
    :persistent-hint="persistentHint"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { baseProps } from '@/objects/Props';
import { toRefs } from 'vue';

const props = defineProps({
    ...baseProps,
    indeterminate: {
        type: Boolean,
        default: false
    },
});
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

defineExpose({
    reset
});
</script>
