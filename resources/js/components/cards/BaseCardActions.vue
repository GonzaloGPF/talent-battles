<template>
  <div class="d-flex justify-space-between w-full">
    <div class="d-flex align-center">
      <app-button
        :title="Translator.actionTitle(open ? 'close' : 'open')"
        :icon="open ? 'mdi-arrow-down-thick' : 'mdi-arrow-up-thick'"
        size="small"
        @click="$emit('open')"
      />
      <app-button
        :title="Translator.actionTitle('share')"
        icon="mdi-share-variant"
        size="small"
        @click="$emit('share')"
      />
      <app-button
        v-if="canShow('buy')"
        :title="Translator.actionTitle('buy')"
        icon="mdi-cart"
        size="small"
        @click="$emit('buy')"
      />
      <app-button
        v-if="canShow('battle')"
        :title="Translator.actionTitle('battle')"
        icon="mdi-sword-cross"
        size="small"
        @click="$emit('battle')"
      />
    </div>
    <ReactionButtons
      v-if="actions?.length"
      :model="model"
      :actions="actions"
    />
  </div>
</template>
<script setup>
import Translator from "@/objects/Translator";
import {defineAsyncComponent} from 'vue'

defineEmits(['open', 'share', 'buy', 'battle']);
const props = defineProps({
    model: {
        type: Object,
        required: true,
        default: () => {
        }
    },
    open: {
        type: Boolean,
        default: false
    },
    actions: {
        type: Array,
        default: () => []
    }
});
const ReactionButtons = defineAsyncComponent(() => import('@/components/buttons/ReactionButtons.vue'));
const AppButton = defineAsyncComponent(() => import('@/components/core/AppButton.vue'));
const canShow = (action) => props.actions.includes(action);
</script>
<style>
.caption-mini {
    font-size: .65rem;
}
</style>
