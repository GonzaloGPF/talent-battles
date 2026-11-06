<template>
  <v-alert
    :density="density"
    :title="title"
    :text="text || message"
    :type="type"
    :outlined="outlined"
    :prominent="prominent"
    :color="color"
    :border="border"
    :border-color="borderColor"
    :closable="closable"
    :class="{blink: blink}"
    :icon="icon"
    :model-value="modelValue"
    :variant="variant"
    class="alert"
  >
    <slot />
  </v-alert>
</template>
<script setup>

defineProps({
    modelValue: {
        type: Boolean,
        default: true
    },
    type: {
        type: String,
        default: 'info',
        validator: value => ['success', 'info', 'warning', 'error'].includes(value)
    },
    variant: {
        type: String,
        default: 'flat',
        validator: value => ['flat', 'elevated', 'tonal', 'outlined', 'text', 'plain'].includes(value)
    },
    title: {
        type: String,
        default: null
    },
    text: {
        type: String,
        default: null
    },
    message: {
        type: String,
        default: null
    },
    density: {
        type: String,
        default: null,
        validator: value => ['default', 'comfortable', 'compact'].includes(value)
    },
    outlined: {
        type: Boolean,
        default: false
    },
    prominent: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: undefined
    },
    icon: {
        type: String,
        default: null
    },
    border: {
        type: String,
        default: null,
        validator: value => ['start', 'top', 'end', 'bottom'].includes(value)
    },
    borderColor: {
        type: String,
        default: null,
        validator: value => ['success', 'warning', 'info', 'danger'].includes(value)
    },
    // transition: {
    //     type: String,
    //     default: null
    // },
    closable: {
        type: Boolean,
        default: false
    },
    blink: {
        type: Boolean,
        default: false
    },
});

</script>
<style>
.alert.blink {
    animation-name: blink;
    animation-duration: 20s;
    animation-timing-function: linear;
    animation-iteration-count: infinite;

    -webkit-animation-name: blink;
    -webkit-animation-duration: 2s;
    -webkit-animation-timing-function: linear;
    -webkit-animation-iteration-count: infinite;
}

@keyframes blink {
    0% {
        opacity: 1.0;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        opacity: 1.0;
    }
}
</style>
