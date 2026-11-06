<template>
  <v-autocomplete
    ref="auto"
    v-model="iValue"
    v-model:search="search"
    :name="iName"
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
    :required="required"
    :class="{required}"
    :item-title="item => item[urlAttribute] || item.label || item.name"
    item-value="id"
    @click:clear="reset"
  />
</template>
<script setup>
import { baseProps } from '@/objects/Props';
import useAutoComplete from '@/composables/useAutoComplete';
import useInput from '@/composables/useInput';
import { ref, toRefs, watch } from 'vue';

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
});
const emits = defineEmits([
    'update:modelValue',
    'update:selected',
    'clear',
])
const search = ref(null);
const { errors, iLabel, iName, iValue, reset } = useInput(toRefs(props));
const {
    loading,
    items,
    selectedItems,
} = useAutoComplete(iValue, props.src, props.multiple, search, props.params, props.urlAttribute);

watch(iValue, (value) => {
    emits('update:modelValue', value);
    emits('update:selected', selectedItems.value);
});
defineExpose({
    reset
});
</script>
