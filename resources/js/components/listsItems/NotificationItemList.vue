<template>
  <v-list-item>
    <template #prepend>
      <v-badge
        :content="isActive"
        :model-value="isActive > 0"
        :class="isActive ? 'cursor-pointer' : null"
        color="green"
        class="cursor-pointer"
        @click.stop="onClick"
      >
        <app-icon
          :title="Translator.modelTitle('notification', true)"
          :color="isActive ? undefined : 'grey-darken-1'"
          icon="$notification"
        />
      </v-badge>
    </template>
    <v-list-item-title>
      <notifications-menu
        ref="notificationsMenuElement"
        class="ml-3"
        @items="items = $event"
      />
    </v-list-item-title>
  </v-list-item>
</template>
<script setup>
import AppIcon from '@/components/core/AppIcon.vue';
import NotificationsMenu from '@/components/menu/NotificationsMenu.vue';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';

const items = ref([]);
const notificationsMenuElement = ref(null);

const isActive = computed(() => items.value?.length);

const onClick = () => notificationsMenuElement.value?.toggleMenu();
</script>