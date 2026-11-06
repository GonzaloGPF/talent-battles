<template>
  <v-select
    v-model="iValue"
    :label="iLabel"
    :name="iName"
    :items="items"
    :append-icon="appendIcon"
    :prepend-icon="prependIcon"
    :error-messages="errors"
    :disabled="disabled"
    :filter="filter"
    :multiple="multiple"
    :hint="hint"
    :chips="chips"
    :persistent-hint="persistentHint"
    :clearable="clearable"
    :dense="dense"
    :class="{required}"
    :required="required"
    :item-title="item => item.nick_name || item.label || item.name"
    item-value="id"
    @click:clear="reset"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import {ref, toRefs, watch} from 'vue';
import useSelect from '@/composables/useSelect';
import {baseProps} from '@/objects/Props';
import Utils from '@/objects/Utils';

const props = defineProps({
  ...baseProps,
  src: {
    type: String,
    default: null
  },
  filter: {
    type: Function,
    default: () => true
  },
  multiple: {
    type: Boolean,
    default: false
  },
  chips: {
    type: Boolean,
    default: false
  },
  clearable: {
    type: Boolean,
    default: true
  },
  options: {
    type: Array,
    default: null
  },
  variant: {
    type: String,
    default: null,
    validator: value => ['solo', 'underlined'].includes(value)
  }
});
const search = ref(null);
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));
const {
  items,
  selectedItems,
} = useSelect(iValue, props.src, props.multiple, search, props.filter, props.options);

const emits = defineEmits([
  'update:modelValue',
  'update:selected',
  'clear',
]);

watch(iValue, (newValue, oldValue) => {
  if (Utils.areEquals(newValue, oldValue)) return;
  emits('update:modelValue', iValue.value);
  emits('update:selected', selectedItems.value);
});

defineExpose({
  reset
});

</script>
<style>
.select-searcher .v-input__slot {
  margin: 0
}

.select-searcher .v-text-field__details {
  display: none;
}

.select-all-checkbox div.v-input--selection-controls__input {
  margin-right: 8px !important;
}
</style>
