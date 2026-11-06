<template>
  <app-modal
    :model-value="show"
    :title="title"
    :persistent="false"
    :ok-text="okText"
    :cancel-text="cancelText"
    width="600px"
    @ok="onClick(true)"
    @close="onClick(false)"
  >
    {{ message }}

    <component
      :is="component"
      v-if="component"
      ref="form"
      class="my-3"
    />
  </app-modal>
</template>
<script setup>
import AppModal from '@/components/core/AppModal.vue';
import EventBus from '@/events/EventBus.js';
import EventTypes from '@/events/EventTypes.js';
import { useConfirm } from '@/stores/confirm';
import { storeToRefs } from 'pinia';
import { ref } from 'vue';

const { show, title, message, okText, cancelText, component } = storeToRefs(useConfirm());

const form = ref(null);

function onClick(value) {
    if (value && form.value) {
        value = form.value.validate();

        if (! value) return;
    }

    EventBus.emit(EventTypes.confirmed, value);
}
</script>
