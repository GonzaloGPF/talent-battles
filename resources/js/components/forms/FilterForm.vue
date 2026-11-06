<template>
  <app-form hide-feedback>
    <v-row>
      <v-col
        v-for="input in vInputs"
        :key="getKey(input)"
        :cols="12"
        :md="6"
        :lg="4"
        :xl="3"
      >
        <!-- All Inputs -->
        <component
          :is="inputsMap.getComponent(input.input)"
          :ref="setRef"
          :model-value="input.value"
          :name="input.name"
          :label="input.label"
          :options="input.options"
          :src="input.src"
          :filter="input.filter"
          :multiple="input.multiple"
          :params="input.params"
          :url-attribute="input.urlAttribute"
          :type="input.type"
          :min="input.min"
          :max="input.max"
          :class="{'mt-6 pl-2': input.type === 'check', 'd-none': input.name === 'page'}"
          :range="input.range"
          :clearable="true"
          :width="290"
          hide-details
          class="animate__animated animate__fadeIn mx-1"
          @keyup="$emit('keyup', $event)"
          @update:model-value="onModelValue(input, $event)"
        />
      </v-col>
    </v-row>
  </app-form>
</template>
<script setup>
import { computed, reactive, watch } from 'vue';
import AppForm from '@/components/core/AppForm.vue';
import _ from 'lodash';
import Utils from '@/objects/Utils';
import inputsMap from '@/components/forms/inputs/InputsMap';

const emits = defineEmits(['keyup', 'update:modelValue', 'update:filterText']);
const props = defineProps({
    modelValue: {
        type: Object,
        default: null
    },
    inputs: {
        type: Array,
        default: () => []
    },
});
const vInputs = computed(() => {
    return _.chain(props.inputs)
        .filter(input => ! input.name.startsWith('max_'))
        .map(input => ({...input, label: getLabel(input)}))
        .sortBy('label')
        .value()
});
const form = reactive(props.modelValue);
const inputRefs = [];
const filterText = computed(() =>
    _.chain(form)
        .keys()
        .filter(key => !Utils.isEmptyValue(form[key]) && key !== 'page')
        .map(input => input.label)
        .uniq()
        .join(', ')
        .value()
);

function getKey (input) {
    return `${input.name}_${input.input || 'text'}`;
}

function onModelValue (input, value) {
    if (input.input === 'check' && ! value) {
        return form[input.name] = null;
    }

    if (input.range && Array.isArray(value)) {
        const [min, max] = value;
        form[input.name] = min;
        form[input.name.replace('min_', 'max_')] = max;
    } else {
        form[input.name] = value;
    }
}

function reset () {
    inputRefs
        .filter(inputRef => inputRef?.reset)
        .forEach(inputRef => inputRef.reset());
}

function getLabel (input) {
    if (input.label) {
        return typeof input.label === 'function'
            ? input.label()
            : input.label;
    }

    return undefined;
}

function setRef(el) {
    inputRefs.push(el);
}

watch(form, (newValue, oldValue) => {
    if (Utils.areEquals(newValue, oldValue)) return;

    emits('update:modelValue', newValue);
    emits('update:filterText', filterText.value);
}, { deep: true, immediate: true });

defineExpose({ reset });
</script>
