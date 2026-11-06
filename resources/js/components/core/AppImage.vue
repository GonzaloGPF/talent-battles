<template>
  <v-lazy>
    <v-img
      v-bind="$attrs"
      :src="vSrc"
      :lazy-src="lazySrc || '/images/logo.svg'"
      :alt="file?.name || alt"
      :gradient="gradient"
      :aspect-ratio="aspectRatio"
      :contain="vContain"
      :width="width"
      :height="height"
      :max-width="maxWidth"
      :max-height="maxHeight"
      :style="style"
      :class="backdrop ? 'backdrop-image' : null"
      content-class="z-10 -mb-px"
      @load="$emit('load')"
      @click.stop="$emit('click', vSrc)"
    >
      <template #placeholder>
        <v-row
          class="fill-height m-0"
          align="center"
          justify="center"
        >
          <v-progress-circular
            :indeterminate="true"
            color="grey lighten-5"
          />
        </v-row>
      </template>
      <slot />
    </v-img>
  </v-lazy>
</template>
<script setup>
import {computed} from "vue";
import Utils from '@/objects/Utils';

defineEmits([
  'load',
  'click',
]);
const props = defineProps({
  file: {
    type: Object,
    default: null
  },
  src: {
    type: String,
    default: null
  },
  lazySrc: {
    type: String,
    default: null
  },
  gradient: {
    type: String,
    default: null
  },
  aspectRatio: {
    type: String,
    default: null
  },
  width: {
    type: [String, Number],
    default: null
  },
  maxWidth: {
    type: [String, Number],
    default: null
  },
  height: {
    type: [String, Number],
    default: null
  },
  maxHeight: {
    type: [String, Number],
    default: null
  },
  contain: {
    type: Boolean,
    default: null
  },
  alt: {
    type: String,
    default: null
  },
  backdrop: {
    type: Boolean,
    default: false
  },
});
const vSrc = computed(() => props.src || Utils.getFileURL(props.file));

const style = computed(() => ({
  backgroundImage: props.backdrop ? `url(${ vSrc.value })` : null,
  backgroundRepeat: 'no-repeat',
  backgroundPosition: 'center',
  backgroundSize: 'cover'
}));

const vContain = computed(() => {
  if (! vSrc.value) return false;

  return props.contain || vSrc.value.includes('logo.svg');
});

// const hasClickListener = computed(() => $listeners && $listeners.click);
</script>
<style>
.backdrop-image .v-img__img {
  backdrop-filter: blur(15px);
}
</style>