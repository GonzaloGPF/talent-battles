<template>
  <v-list
    :selected="[currentFlag.locale]"
    :items="items"
    density="compact"
    @update:selected="$emit('click', $event[0])"
  >
    <v-list-item
      v-for="item in items"
      :key="item.value"
      :title="item.title"
      :value="item.value"
    >
      <template #prepend>
        <app-icon
          :icon="item.icon"
          :size="xs ? 'small' : undefined"
          class="mr-3"
        />
      </template>
    </v-list-item>
  </v-list>
</template>
<script setup>
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import AppIcon from '@/components/core/AppIcon.vue';
import { useDisplay } from 'vuetify';

const props = defineProps({
    flagsData: {
        type: Array,
        default: () => []
    }
});
const { currentFlag } = storeToRefs(useAuth());
const { xs } = useDisplay();
const items = props.flagsData.map(flagData => ({
    title: flagData.label,
    value: flagData.locale,
    icon: flagData.icon,
}));

defineEmits(['click']);
</script>
