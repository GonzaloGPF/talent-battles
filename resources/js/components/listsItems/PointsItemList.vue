<template>
  <v-list-item>
    <template #prepend>
      <v-badge
        :content="user?.points"
        :model-value="user?.points > 0"
        color="transparent"
        class="cursor-pointer"
        @click.stop="onClick"
      >
        <app-icon
          :title="Translator.tl('points')"
          icon="$points"
        />
      </v-badge>
    </template>
    <v-list-item-title>
      <buy-points-button
        ref="pointsButton"
        class="ml-3"
      />
    </v-list-item-title>
  </v-list-item>
</template>
<script setup>
import BuyPointsButton from '@/components/buttons/BuyPointsButton.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import AppIcon from '@/components/core/AppIcon.vue';
import Translator from '@/objects/Translator';
import { ref } from 'vue';

const { user } = storeToRefs(useAuth());
const pointsButton = ref(null);

const onClick = () => pointsButton.value?.toggleModal();
</script>