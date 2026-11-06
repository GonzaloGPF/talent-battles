<template>
  <div
    :style="style"
    :title="label"
    :class="{'d-inline': inline}"
    class="overflow-hidden"
  >
    <AppIcon
      v-if="icon"
      :icon="icon"
      :class="{'mr-2': label}"
      size="small"
    />

    <div
      v-if="label"
      :class="{'d-inline': inline, 'mr-2': inline }"
      class="font-weight-bold"
    >
      <v-tooltip
        v-if="tooltip"
        location="top"
        class="ml-1 d-inline-block"
      >
        <template #activator="{ props: tooltipProps }">
          <app-button
            icon="mdi-information"
            size="x-small"
            v-bind="tooltipProps"
          />
        </template>
        <span v-text="tooltip" />
      </v-tooltip>

      <span v-text="label" />
    </div>

    <slot>
      <field-value
        v-if="modelValue !== undefined"
        :model-value="modelValue"
        :type="type"
        :route-data="routeData"
        :disabled="disabled"
        :class="classes"
        :closable="closable"
        :clean="clean"
        :color="color"
        :format="format"
        :constant="constant"
        class="field-value"
        @close="$emit('close', $event)"
      />
    </slot>
  </div>
</template>
<script setup>
import {computed, defineAsyncComponent} from 'vue';

const FieldValue = defineAsyncComponent(() => import('@/components/core/FieldValue.vue'))
const AppIcon = defineAsyncComponent(() => import('@/components/core/AppIcon.vue'))
const AppButton = defineAsyncComponent(() => import('@/components/core/AppButton.vue'))

defineEmits(['close']);
const props = defineProps({
    label: {
        type: String,
        default: null
    },
    modelValue: {
        type: [String, Number, Boolean, Array, Object],
        default: null
    },
    icon: {
        type: String,
        default: null
    },
    inline: {
        type: Boolean,
        default: false
    },
    tooltip: {
        type: String,
        default: null
    },
    type: {
        type: String,
        default: 'text'
    },
    routeData: {
        type: Object,
        default: null
    },
    disabled: {
        type: Boolean,
        default: false
    },
    closable: {
        type: Boolean,
        default: false
    },
    clean: {
        type: Boolean,
        default: false
    },
    error: {
        type: Boolean,
        default: false
    },
    warning: {
        type: Boolean,
        default: false
    },
    success: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: null
    },
    format: {
        type: String,
        default: null
    },
    constant: {
        type: String,
        default: null
    },
});
const classes = computed(() => ({
    'd-inline-block': props.inline,
    'text-red': props.error,
    'text-yellow': props.warning,
    'text-green': props.success,
}));
const style = computed(() => ({
        'min-height': props.inline || ! props.label ? 'none' : '40px'
    }
));
</script>
