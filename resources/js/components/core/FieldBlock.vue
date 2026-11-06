<template>
  <div class="border border-t-0 m-1 mt-2">
    <div class="d-flex h-0 position-relative">
      <span class="d-block border-t w-100 position-absolute" />
      <span
        class="text-xs mx-1 -mt-2"
        v-text="title"
      />
      <span class="d-block border-t flex-1" />
    </div>
    <div
      v-if="closable"
      class="float-right mr-1"
      style="height: 20px; width: 22px"
      @click="$emit('close')"
    >
      <app-button
        :title="Translator.actionTitle('close')"
        type="text"
        icon="times"
        class="block p-0 px-2"
        size="default"
      />
    </div>

    <div :class="{'mt-1': closable}">
      <slot />
    </div>
  </div>
</template>
<script setup>
import Translator from "@/objects/Translator";
import AppButton from '@/components/core/AppButton.vue';

defineEmits(['close']);
defineProps({
    title: {
        type: String,
        required: true,
        default: null
    },
    closable: {
        type: Boolean,
        default: false
    }
});
</script>
