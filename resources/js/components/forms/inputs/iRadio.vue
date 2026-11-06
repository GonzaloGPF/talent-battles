<template>
  <v-radio
    :label="iLabel"
    :name="iName"
    :value="iValue"
    :disabled="disabled"
    :color="color"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { baseProps } from '@/objects/Props';
import { toRefs } from 'vue';

const props = defineProps({
    ...baseProps,
    // size: {
    //     type: String,
    //     default: 'mini'
    // }
});
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

defineExpose({
    reset
});
</script>
