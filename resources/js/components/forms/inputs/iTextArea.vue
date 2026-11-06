<template>
  <v-textarea
    ref="textAreaElement"
    v-model="iValue"
    :name="iName"
    :label="iLabel"
    :hint="hint"
    :persistent-hint="persistentHint"
    :auto-grow="autoGrow"
    :filled="filled"
    :background-color="bgColor"
    :clearable="clearable"
    :required="required"
    :class="{ required }"
    :disabled="disabled"
    :hide-details="hideDetails"
    :error-messages="errors"
    :counter="counter"
    :rows="rows"
    :max-rows="maxRows"
    @key.enter.stop
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { onMounted, ref, toRefs, watch } from 'vue';
import { baseProps } from '@/objects/Props';

const emits = defineEmits(['update:modelValue']);
const props = defineProps({
  ...baseProps,
  autoGrow: {
    type: Boolean,
    default: null
  },
  filled: {
    type: Boolean,
    default: null
  },
  bgColor: {
    type: String,
    default: null
  },
  clearable: {
    type: Boolean,
    default: null
  },
  counter: {
    type: Number,
    default: null
  },
  rows: {
    type: Number,
    default: null
  },
  maxRows: {
    type: Number,
    default: null
  },
});
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));
const textAreaElement = ref(null)

onMounted(() => textAreaElement.value?.focus());

watch(iValue, (value) => emits('update:modelValue', value));

defineExpose({
  reset
});
</script>
