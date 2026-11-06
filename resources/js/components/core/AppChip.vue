<template>
  <v-chip
    :close="close"
    :close-icon="closeIcon"
    :color="color"
    :text="text"
    :outlined="outlined"
    :draggable="draggable"
    :label="label"
    :size="size"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    @click="$emit('click')"
    @click:close="$emit('close')"
  >
    {{ modelValue.label || modelValue.name || modelValue }}
  </v-chip>
</template>
<script setup>
defineProps({
    modelValue: {
        type: [String, Object],
        required: true,
        default: null
    },
    close: {
        type: Boolean,
        default: null
    },
    closeIcon: {
        type: String,
        default: null
    },
    color: {
        type: String,
        default: null
    },
    text: {
        type: String,
        default: null
    },
    outlined: {
        type: Boolean,
        default: null
    },
    draggable: {
        type: Boolean,
        default: null
    },
    label: {
        type: Boolean,
        default: null
    },
    size: {
        type: String,
        default: undefined,
        validator: value => ['x-small', 'small', 'large', 'x-large'].includes(value)
    },
    prependIcon: {
        type: String,
        default: null,
    },
    appendIcon: {
        type: String,
        default: null,
    }
});
defineEmits([
    'click',
    'close',
]);
</script>
<style>
.v-chip.white {
    color: black;
}
</style>
