<template>
  <v-combobox
    ref="auto"
    v-model="iValue"
    v-model:search="search"
    :label="iLabel"
    :items="items"
    :prepend-icon="prependIcon"
    :error-messages="errors"
    :filter="filter"
    :loading="loading"
    :multiple="multiple"
    :item-text="urlAttribute"
    :disabled="disabled"
    :hint="hint"
    :persistent-hint="persistentHint"
    :clearable="clearable"
    :dense="dense"
    :class="{required}"
    :item-title="item => item[urlAttribute] || item.label || item.name"
    :item-value="itemValue"
  />
</template>
<script setup>
import { baseProps } from '@/objects/Props';
import useAutoComplete from '@/composables/useAutoComplete';
import { ref, toRefs, watch } from 'vue';
import useInput from '@/composables/useInput';
import Utils from '@/objects/Utils';

const emits = defineEmits([
    'update:modelValue',
    'update:selected',
    'clear',
]);
const props = defineProps({
    ...baseProps,
    src: {
        type: String,
        // required: true,
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
    clearable: {
        type: Boolean,
        default: true
    },
    options: {
        type: Array,
        default: null
    },
    urlAttribute: {
        type: String,
        default: 'name'
    },
    params: {
        type: Object,
        default: null
    },
    itemValue: {
        type: String,
        default: 'id'
    }
});

const search = ref(null);
const { errors, iLabel, iValue, reset } = useInput(toRefs(props));
const {
    loading,
    items,
    selectedItems,
} = useAutoComplete(iValue, props.src, props.multiple, search, props.params, props.urlAttribute);

watch(iValue, (newValue, oldValue) => {
    if (Utils.areEquals(newValue, oldValue)) return;

    const parsedValue = props.multiple ? newValue.map(item => item[props.itemValue] || item) : newValue[props.itemValue] || newValue;

    emits('update:modelValue', parsedValue);
    emits('update:selected', selectedItems.value);
});

defineExpose({
    reset
});
</script>
