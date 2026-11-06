<template>
  <v-card
    v-click-outside="reveal = false"
    class="base-card d-flex flex-column"
    :class="{'promoted': model?.is_promoted}"
    :style="style"
  >
    <transition
      mode="out-in"
      enter-active-class="animate__faster animate__fadeIn animate__faster"
      leave-active-class="animate__faster animate__fadeOut animate__faster"
    >
      <v-card-text
        v-if="reveal"
        class="overflow-x-hidden"
        style="color: white"
      >
        <slot
          name="info"
          class="flex-grow-1"
        />
      </v-card-text>
      <div v-else>
        <div
          v-if="chips.length"
          class="absolute z-10 bg-black/60 right-0 space-x-1 p-1 rounded"
        >
          <AppChip
            v-for="chip in chips"
            :key="chip"
            :model-value="chip"
            color="white"
            size="small"
            class="mb-1 md:mb-0"
            label
            @click="$emit('chip', chip)"
          />
        </div>
        <AppImage
          class="text-white align-end rounded-t-xl"
          :style="imageStyle"
          :alt="model?.name"
          :src="imageUrl"
          backdrop
          @click="$emit('image', imageUrl)"
          @load="$emit('image-load', imageUrl)"
        />
        <v-card-title
          v-if="$slots.title"
          class="d-flex justify-space-between py-2 align-center w-full"
          style="background: rgba(0 , 0, 0, .70);"
        >
          <slot name="title" />
        </v-card-title>

        <v-card-subtitle>
          <slot name="subtitle" />
        </v-card-subtitle>

        <v-card-text
          v-if="$slots.default"
          class="overflow-x-hidden"
        >
          <slot />
        </v-card-text>
      </div>
    </transition>

    <v-card-actions
      v-if="actions.length"
      class="mt-auto"
    >
      <ReactionButtons
        :open="reveal"
        :model="model"
        :actions="actions"
        @info="reveal = !reveal"
      />
    </v-card-actions>
  </v-card>
</template>
<script setup>
import {computed, defineAsyncComponent, ref} from "vue";
import Utils from '@/objects/Utils';
import {useRoute} from 'vue-router'

defineEmits(['action', 'chip', 'image', 'image-load']);
const props = defineProps({
  model: {
    type: Object,
    required: true,
    default: null
  },
  chips: {
    type: Array,
    default: () => []
  },
  imageStyle: {
    type: Object,
    default: null
  },
  actions: {
    type: Array,
    default: () => []
  }
});
const AppChip = defineAsyncComponent(() => import('@/components/core/AppChip.vue'));
const ReactionButtons = defineAsyncComponent(() => import('@/components/buttons/ReactionButtons.vue'));
const AppImage = defineAsyncComponent(() => import('@/components/core/AppImage.vue'));
const { path } = useRoute()
const imageUrl = computed(() => Utils.getFileURL(props.model?.image ?? props.model?.album?.image ?? props.model?.createdBy?.image, path.includes('admin')));
const reveal = ref(false);
const style = computed(() => ({
  position: 'relative',
  // color: props.model?.color,
  // height: '440px'
}));
</script>
