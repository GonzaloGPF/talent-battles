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
    :item-value="item => item.id"
    :hint="hint"
    :persistent-hint="persistentHint"
    :clearable="clearable"
    :dense="dense"
    :required="required"
    :class="{required}"
    item-title="label"
    @click:clear="reset"
  />
</template>
<script setup>
import useInput from '@/composables/useInput';
import { computed, toRefs, watch } from 'vue';
import Translator from '@/objects/Translator';
import { baseProps } from '@/objects/Props';

const props = defineProps({
    ...baseProps,
    clearable: {
        type: Boolean,
        default: false
    }
});
const { iLabel, iName, iValue, reset } = useInput(toRefs(props));
const items = computed(() => [
    {
        id: 0,
        label: Translator.tl('no'),
    },
    {
        id: 1,
        label: Translator.tl('yes'),
    }
]);

const emits = defineEmits([
    'update:modelValue',
    'input',
    'focus',
    'blur',
    'mousedown',
    'mouseup',
    'clear',
    'append'
]);

watch(iValue, (value) => {
    emits('update:modelValue', value);
})

defineExpose({
    reset
});
</script>
