<template>
  <div v-if="isArray">
    <AppChip
      v-for="(chip, index) in modelValue"
      :key="chip.id || index"
      :closable="closable"
      :model-value="getChipLabel(chip)"
      :color="color"
      size="small"
      class="mr-1"
      @click="$emit('close', chip)"
      @close="$emit('close', chip)"
    />
    <AppEmpty
      v-if="modelValue.length === 0"
      class="text-sm"
    />
  </div>

  <AppChip
    v-else-if="type === 'chip' && modelValue"
    :closable="closable"
    :model-value="getChipLabel(modelValue)"
    :color="color"
    size="small"
    @click="$emit('close', modelValue)"
    @close="$emit('close', modelValue)"
  />

  <AppText
    v-else-if="!routeData && (type === 'text' || type === 'html')"
    class="text-sm"
    :model-value="modelValue"
  />

  <AppCheck
    v-else-if="type === 'boolean'"
    :disabled="disabled"
    :model-value="modelValue"
  />

  <AppDate
    v-else-if="['date', 'time', 'datetime'].includes(type)"
    :disabled="disabled"
    :model-value="modelValue"
    :format="type === 'time' ? 'hour' : type === 'datetime' ? 'datetime' : format"
  />

  <AppMoney
    v-else-if="type === 'money'"
    :disabled="disabled"
    :clean="clean"
    :model-value="modelValue"
  />

  <AppPercent
    v-else-if="type === 'percent'"
    :disabled="disabled"
    :model-value="modelValue"
  />

  <AppButton
    v-else-if="(type === 'link' || type === 'email') && modelValue"
    :label="modelValue"
    :disabled="disabled"
    color="blue"
    size="small"
    append-icon="mdi-open-in-new"
    variant="text"
    @click="onLink(modelValue)"
  />

  <AppButton
    v-else-if="routeData"
    :disabled="disabled"
    :label="modelValue"
    :color="color"
    size="small"
    variant="text"
    class="text-left d-inline"
    @click="$router.push(routeData)"
  />

  <span
    v-else
    v-text="modelValue"
  />
</template>
<script setup>
import {computed, defineAsyncComponent} from "vue";
import Utils from '@/objects/Utils';
import Translator from '@/objects/Translator';

defineEmits(['close']);
const props = defineProps({
    modelValue: {
        type: [String, Number, Array, Boolean, Object],
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
        default: null
    },
    closable: {
        type: Boolean,
        default: false
    },
    clean: {
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
    }
});
const AppButton = defineAsyncComponent(() => import('@/components/core/AppButton.vue'));
const AppChip = defineAsyncComponent(() => import('@/components/core/AppChip.vue'));
const AppText = defineAsyncComponent(() => import('@/components/core/AppText.vue'));
const AppCheck = defineAsyncComponent(() => import('@/components/core/AppCheck.vue'));
const AppDate = defineAsyncComponent(() => import('@/components/core/AppDate.vue'));
const AppMoney = defineAsyncComponent(() => import('@/components/core/AppMoney.vue'));
const AppPercent = defineAsyncComponent(() => import('@/components/core/AppPercent.vue'));
const AppEmpty = defineAsyncComponent(() => import('@/components/core/AppEmpty.vue'));
const isArray = computed(() => Array.isArray(props.modelValue));
const onLink = (url) => {
    if (props.type === 'email') {
        return document.location = `mailto:${ url }`;
    }

    return Utils.openTab(url);
}

const getChipLabel = (chip) => {
    if (props.constant) {
        return Translator.tConstName(props.constant, chip);
    }

    return chip.label || chip.name || chip;
}
</script>
