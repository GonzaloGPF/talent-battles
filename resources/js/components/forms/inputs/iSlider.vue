<template>
  <v-range-slider
    v-if="range"
    v-model="iValue"
    :name="iName"
    :label="iLabel"
    :thumb-color="color"
    :thumb-label="thumbLabel"
    :step="step"
    :ticks="ticks"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    :readonly="readonly"
    :min="min"
    :max="max"
    :thumb-size="thumbSize"
    :tick-size="tickSize"
    :hint="hint"
    :persistent-hint="persistentHint"
    :vertical="vertical"
    :track-color="trackColor"
    :hide-details="hideDetails"
    @click:append="$emit('append')"
    @click:prepend="$emit('prepend')"
  />
  <v-slider
    v-else
    v-model="iValue"
    :label="iLabel"
    :thumb-color="color"
    :thumb-label="thumbLabel"
    :step="step"
    :ticks="ticks"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    :readonly="readonly"
    :min="min"
    :max="max"
    :thumb-size="thumbSize"
    :tick-size="tickSize"
    :hint="hint"
    :persistent-hint="persistentHint"
    :vertical="vertical"
    :track-color="trackColor"
    :hide-details="hideDetails"
    @click:append="$emit('append')"
    @click:prepend="$emit('prepend')"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { baseProps } from '@/objects/Props';
import { toRefs } from 'vue';

defineEmits([
    'append',
    'prepend'
])
const props = defineProps({
    ...baseProps,
    min: {
        type: Number,
        default: null
    },
    max: {
        type: Number,
        default: null
    },
    range: {
        type: Boolean,
        default: null
    },
    thumbLabel: {
        type: String,
        default: 'always'
    },
    step: {
        type: Number,
        default: 1
    },
    ticks: {
        type: Boolean,
        default: null
    },
    thumbSize: {
        type: Number,
        default: null
    },
    tickSize: {
        type: Number,
        default: null
    },
    vertical: {
        type: Boolean,
        default: null
    },
    trackColor: {
        type: String,
        default: null
    },
});
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));

defineExpose({
    reset
});
</script>
