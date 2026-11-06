<template>
  <div>
    <i-text
      id="i-date-picker-text"
      :model-value="Formatter.date(iValue)"
      :name="iName"
      :label="iLabel"
      prepend-icon="mdi-calendar"
      readonly
      clearable
      @clear="reset"
    />
    <v-menu
      activator="#i-date-picker-text"
      offset-y
    >
      <v-date-picker
        v-model="iValue"
        :name="iName"
        :color="color"
        :range="range"
        :elevation="elevation"
        :width="width"
        :landscape="landscape"
        :min="min"
        :max="max"
        :title="iLabel"
        :format="locales.dateFormat"
        :multiple="multiple"
        hide-actions
      />
    </v-menu>
  </div>
</template>
<script setup>
import useInput from '@/composables/useInput';
import { toRefs, watch } from 'vue';
import { baseProps } from '@/objects/Props';
import IText from '@/components/forms/inputs/iText.vue';
import Formatter from '@/objects/Formatter';
import locales from '@/config/locales';

const emits = defineEmits(['update:model-value']);
const props = defineProps({
  ...baseProps,
  hideMenu: {
    type: Boolean,
    default: null,
  },
  range: {
    type: String,
    default: undefined,
    validator: value => ['end', 'start'].includes(value)
  },
  multiple: {
    type: Boolean,
    default: false
  },
  min: {
    type: String,
    default: null,
  },
  max: {
    type: String,
    default: null
  },
  allowedDates: {
    type: [Array, Object, Function],
    default: null
  },
  elevation: {
    type: Number,
    default: null
  },
  showCurrent: {
    type: [Boolean, String],
    default: null
  },
  fullWidth: {
    type: Boolean,
    default: null
  },
  width: {
    type: [String, Number],
    default: null
  },
  events: {
    type: [Array, Function],
    default: null
  },
  noTitle: {
    type: Boolean,
    default: false
  },
  landscape: {
    type: Boolean,
    default: null
  },
  selectedItemsText: {
    type: String,
    default: undefined
  },
  type: {
    type: String,
    default: 'date',
    validator: value => ['date', 'month'].includes(value)
  },
});
const { iLabel, iName, iValue } = useInput(toRefs(props));

watch(iValue, event => {
  if (props.multiple) {
    emits('update:model-value', event)
  } else if (Array.isArray(event) && ! props.multiple) {
    emits('update:model-value', event[0])
  }
});

const reset = () => {
  if (props.multiple) {
    iValue.value = []
  } else {
    iValue.value = null;
  }
}

defineExpose({
  reset
});
</script>
