<template>
  <v-dialog
    :model-value="show"
    :width="width"
    :height="height"
    :min-width="minWidth"
    :min-height="minHeight"
    :max-width="maxWidth"
    :max-height="maxHeight"
    :persistent="persistent"
    :transition="transition"
    :fullscreen="smAndDown ? true : fullscreen"
    :content-class="smAndDown ? null : {'w-75': !width, '!max-w-5xl': !maxWidth}"
    class="max-auto"
    @keydown.esc.stop="$emit('close')"
    @update:return-value="$emit('close')"
  >
    <v-card
      :loading="loading"
      class="w-full"
      :min-width="minWidth"
      :min-height="minHeight"
      :max-width="maxWidth"
      :max-height="maxHeight"
    >
      <v-card-title class="brand-font d-flex justify-space-between align-center">
        <span
          v-if="title"
          v-text="title"
        />
        <app-button
          :title="Translator.actionTitle('close')"
          icon="mdi-close"
          class="ml-auto"
          variant="text"
          @click="$emit('close')"
        />
      </v-card-title>

      <v-card-text v-if="modelValue" class="px-10">
        <slot name="default" />
      </v-card-text>

      <v-card-actions v-if="! hideActions">
        <v-spacer />
        <slot name="actions">
          <app-button
            :label="cancelText || Translator.actionTitle('cancel')"
            :title="cancelText || Translator.actionTitle('cancel')"
            size="small"
            variant="tonal"
            class="brand-font"
            @click="$emit('close')"
          />
          <app-button
            :label="okText || Translator.actionTitle('ok')"
            :disabled="disabled || loading"
            :loading="loading"
            :title="okText || Translator.actionTitle('ok')"
            size="small"
            variant="tonal"
            class="brand-font"
            @click="$emit('ok')"
          />
        </slot>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
<script setup>
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { toRef, watch } from 'vue';
import { useErrors } from '@/stores/errors';
import { useDisplay } from 'vuetify';

defineEmits([
    'close',
    'ok',
]);
const props = defineProps({
    modelValue: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: null
    },
    okText: {
        type: String,
        default: null
    },
    cancelText: {
        type: String,
        default: null
    },
    width: {
        type: [Number, String],
        default: null
    },
    height: {
        type: [Number, String],
        default: null
    },
    minWidth: {
        type: [Number, String],
        default: null
    },
    minHeight: {
        type: [Number, String],
        default: null
    },
    maxWidth: {
        type: [Number, String],
        default: null
    },
    maxHeight: {
        type: [Number, String],
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    },
    persistent: {
        type: Boolean,
        default: true
    },
    disabled: {
        type: Boolean,
        default: false
    },
    transition: {
        type: String,
        default: undefined
    },
    hideActions: {
        type: Boolean,
        default: false
    },
    fullscreen: {
        type: Boolean,
        default: false
    },
});
const show = toRef(props, 'modelValue');
const { setErrors } = useErrors();
const { smAndDown } = useDisplay();

watch(show, () => setErrors());
</script>
