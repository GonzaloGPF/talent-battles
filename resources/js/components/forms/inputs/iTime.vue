<template>
  <v-time-picker
    v-model="iValue"
    :name="iName"
    :label="iLabel"
    :allowed-hours="allowedHours"
    :allowed-minutes="allowedMinutes"
    :scrollable="scrollable"
    :use-seconds="useSeconds"
    :ampm-in-title="amPmInTitle"
    :min="min"
    :max="max"
    :color="color"
    :header-color="HeaderColor"
    :landscape="$vuetify.breakpoint.smAndUp"
    :disabled="disabled"
    :readonly="readOnly"
    :elevation="elevation"
    :no-title="noTitle"
    :full-width="fullWidth"
    format="24hr"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { baseProps } from '@/objects/Props';
import { toRefs } from 'vue';

const props = defineProps({
    ...baseProps,
    allowedHours: {
        type: Function,
        default: null
    },
    allowedMinutes: {
        type: Function,
        default: null
    },
    min: {
        type: String,
        default: null,
    },
    max: {
        type: String,
        default: null,
    },
    scrollable: {
        type: Boolean,
        default: null,
    },
    amPmInTitle: {
        type: Boolean,
        default: null,
    },
    headerColor: {
        type: String,
        default: null
    },
    elevation: {
        type: String,
        default: null
    },
    noTitle: {
        type: Boolean,
        default: null
    },
    readOnly: {
        type: Boolean,
        default: null
    },
    useSeconds: {
        type: Boolean,
        default: null
    },
    fullWidth: {
        type: Boolean,
        default: null
    },
    width: {
        type: String,
        default: null
    }
});
const { iLabel, iName, iValue } = useInput(toRefs(props));
</script>
