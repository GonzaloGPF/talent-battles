<template>
  <v-btn-toggle
    :background-color="bgColor"
    :color="color"
    :divided="divided"
    :multiple="multiple"
    :variant="variant"
    :mandatory="mandatory"
  >
    <app-button
      v-for="(button, i) in vButtons"
      :key="i"
      :color="color || button.color"
      :title="getTitle(button)"
      :disabled="disabled"
      :loading="loading"
      :class="button"
      :icon="button.icon"
      :variant="variant"
      :size="size"
      @click="$emit('click', button.name, $event)"
    />
  </v-btn-toggle>
</template>
<script setup>
import buttonsData from '@/config/buttons';
import {computed} from 'vue';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import Formatter from '@/objects/Formatter';

defineEmits(['click']);
const props = defineProps({
    buttons: {
        type: Array,
        default: () => []
    },
    disabled: {
        type: Boolean,
        default: false
    },
    loading: {
        type: Boolean,
        default: false
    },
    color: {
        type: String,
        default: null
    },
    bgColor: {
        type: String,
        default: null
    },
    divided: {
        type: Boolean,
        default: false
    },
    multiple: {
        type: Boolean,
        default: false
    },
    shaped: {
        type: Boolean,
        default: false
    },
    mandatory: {
        type: Boolean,
        default: false
    },
    customButtonsData: {
        type: Object,
        default: null
    },
    variant: {
        type: String,
        default: 'text',
        validator: value => ['text', 'plain', 'outlined'].includes(value)
    },
    size: {
        type: String,
        default: undefined,
        validator: value => ['x-small', 'small', 'large', 'x-large'].includes(value)
    },
});
const mergedButtonsData = {
    ...buttonsData,
    ...props.customButtonsData
};
const vButtons = computed(() => {
    return props.buttons
        .map(button => ({ ...mergedButtonsData[button], ...{ name: button } }))
        .filter(button => button)
});
const getTitle = (button) => {
    const title = getButton(button).title;

    if (typeof title === 'function') {
        return title();
    }

    const path = 'actions.' + getButton(button).title;

    if (Translator.te(path)) {
        return Translator.t(path);
    }

    return Formatter.ucFirst(getButton(button).title)
}
const getButton = (button) => {
    const buttonData = mergedButtonsData[button.title];

    if (! buttonData) throw `No ButtonData found for ${ button.title }`;

    return buttonData;
}
</script>
