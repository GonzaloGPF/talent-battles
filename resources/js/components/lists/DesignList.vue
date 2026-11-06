<template>
  <v-row v-if="designs.length">
    <v-col
      v-for="design in designs"
      :key="design.id"
      :cols="12"
      :lg="6"
      :xl="4"
    >
      <design-card
        :design="design"
        :actions="DesignHelper.actionButtons"
      />
    </v-col>
  </v-row>
  <app-empty
    v-else
    label="design"
  />
</template>
<script setup>
import DesignCard from '@/components/cards/DesignCard.vue';
import AppEmpty from '@/components/core/AppEmpty.vue';
import DesignHelper from '@/helpers/DesignHelper';

defineProps({
    designs: {
        type: Array,
        default: () => []
    },
});
</script>
