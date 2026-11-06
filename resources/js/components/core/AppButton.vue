<template>
  <v-btn
    ref="buttonElement"
    :color="vColor"
    :title="title || label"
    :disabled="disabled"
    :loading="loading"
    :variant="variant"
    :block="block"
    :icon="icon"
    :prepend-icon="prependIcon"
    :append-icon="appendIcon"
    :stacked="stacked"
    :rounded="rounded"
    :elevation="elevation"
    :size="size"
    :class="density === 'compact' ? 'v-btn--density-compact' : null"
    @mouseover.stop="$emit('mouseover', $event)"
    @mouseenter.stop="$emit('mouseenter', $event)"
    @mouseleave.stop="$emit('mouseleave', $event)"
    @click.stop="onClick"
  >
    <slot>
      <AppIcon
        v-if="icon"
        :icon="icon"
        :size="size"
      />
      <span
        v-if="label"
        :class="{'ml-2': icon && !stacked}"
        v-text="label"
      />
    </slot>
    <AppModal
      v-if="modalData"
      v-bind="modalData.data"
      v-on="modalData.listeners"
    >
      <slot name="modal" />
    </AppModal>

    <slot name="menu" />
  </v-btn>
</template>
<script setup>
import {computed, defineAsyncComponent, ref} from 'vue';
import {useRouter} from 'vue-router';

const emits = defineEmits([
  'mouseover',
  'mouseenter',
  'mouseleave',
  'click',
]);
const props = defineProps({
  color: {
    type: String,
    default: null
  },
  icon: {
    type: [String, Boolean],
    default: null
  },
  prependIcon: {
    type: [String, Boolean],
    default: null
  },
  appendIcon: {
    type: [String, Boolean],
    default: null
  },
  title: {
    type: [String, Number],
    default: null
  },
  label: {
    type: [String, Number],
    default: null
  },
  variant: {
    type: String,
    default: undefined,
    validator: value => ['flat', 'tonal', 'outlined', 'text', 'plain'].includes(value)
  },
  elevation: {
    type: String,
    default: null
  },
  disabled: {
    type: Boolean,
    default: null,
  },
  loading: {
    type: Boolean,
    default: null,
  },
  block: {
    type: Boolean,
    default: null,
  },
  stacked: {
    type: Boolean,
    default: null
  },
  rounded: {
    type: [String, Number],
    default: null
  },
  size: {
    type: String,
    default: undefined,
    validator: value => ['x-small', 'small', 'large', 'x-large'].includes(value)
  },
  route: {
    type: Object,
    default: null
  },
  density: {
    type: String,
    default: undefined,
    validator: value => ['comfortable', 'compact'].includes(value)
  },
  modal: {
    type: Object,
    default: null
  },
});
const AppIcon = defineAsyncComponent(() => import('@/components/core/AppIcon.vue'))
const AppModal = defineAsyncComponent(() => import('@/components/core/AppModal.vue'))
const buttonElement = ref();
const router = useRouter();
const vColor = computed(() => {
  if (props.disabled) return null;

  return props.color;
});
const modalData = computed(() => props.modal)

function onClick(event) {
  if (props.route) {
    return router.push(props.route);
  }
  emits('click', event);
}

function click() {
  buttonElement.value?.$el?.click();
}

defineExpose({
  click
});
</script>
