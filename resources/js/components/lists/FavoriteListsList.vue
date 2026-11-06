<template>
  <v-list
    v-if="items.length"
    :items="items"
    density="compact"
    @click:select="onClick"
  />
  <app-empty
    v-else
    model="favoriteList"
  />
</template>
<script setup>
import { computed } from 'vue';
import AppEmpty from '@/components/core/AppEmpty.vue';

const emits = defineEmits(['click'])
const props = defineProps({
    favoriteLists: {
        type: Array,
        default: () => []
    }
});

const items = computed(() =>
    props.favoriteLists
        .map(favoriteList => ({
            title: getTitle(favoriteList),
            value: favoriteList.id,
        }))
);

function getTitle(favoriteList) {
    let title = favoriteList.name;
    const favoritesCount = favoriteList.favorites_count;

    if (favoritesCount) {
        title += ` (${ favoritesCount })`
    }

    return title;
}

function onClick(event) {
    // const item = items.value.find(item => item.value === event.id);
    // emits('click', item);
    emits('click', event.id);
}
</script>
