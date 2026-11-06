<template>
  <v-radio-group
    v-model="iValue"
    :title="iLabel"
    :column="column"
    :mandatory="mandatory"
    :disabled="disabled"
    :hint="hint"
    :persistent-hint="persistentHint"
  >
    <slot />
  </v-radio-group>
</template>
<script setup>
import useInput from '@/composables/useInput';
import { baseProps } from '@/objects/Props';
import { toRefs, watch } from 'vue';

const emits = defineEmits(['update:modelValue']);
const props = defineProps({
    ...baseProps,
    // size: {
    //     type: String,
    //     default: 'mini'
    // }
    column: {
        type: Boolean,
        default: null
    },
    mandatory: {
        type: Boolean,
        default: null
    }
});
const { iValue, iLabel, reset } = useInput(toRefs(props));

watch(iValue, value => emits('update:modelValue', value));

defineExpose({
    reset
});
</script>
