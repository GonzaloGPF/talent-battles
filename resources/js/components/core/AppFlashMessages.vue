<template>
  <div
    v-if="flashMessages && flashMessages.length"
    class="messages-container"
  >
    <TransitionGroup
      name="message"
      tag="ul"
      appear
      style="list-style: none; position: relative; min-width: 300px; min-height: 300px"
    >
      <li
        v-for="flashMessage in flashMessages"
        :key="flashMessage.message"
        class="my-2"
      >
        <app-alert
          :title="flashMessage.title"
          :message="flashMessage.message"
          :type="flashMessage.type"
          :closable="flashMessage.closable"
          :border="flashMessage.closable"
          :icon="flashMessage.icon"
          :variant="flashMessage.variant"
          density="compact"
          style="width: 300px"
        />
      </li>
    </TransitionGroup>
  </div>
</template>
<script setup>
import AppAlert from '@/components/core/AppAlert.vue';
import { useFlashMessages } from '@/stores/flashMessages';
import { storeToRefs } from 'pinia';

const { flashMessages } = storeToRefs(useFlashMessages());
</script>
<style>
.messages-container {
    position: fixed;
    top: 15px;
    left: 0;
    right: 0;
    width: 500px;
    margin: auto;
    max-width: calc(100% - 30px);
    z-index: 9999;
    overflow: hidden;
    transition: all 0.5s ease;
}

.message-move,
.message-enter-active,
.message-leave-active {
    transition: all 0.5s ease;
}

.message-enter-from,
.message-leave-to {
    opacity: 0;
    transform: translateY(-50px);
}

.message-leave-active {
    position: absolute;
}
</style>
