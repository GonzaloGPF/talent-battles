<template>
  <v-icon
    v-if="isIcon"
    :size="size"
    :icon="icon"
    :color="color"
  />
  <app-flag
    v-else
    :icon="icon"
    :size="size"
  />
</template>
<script setup>
// import FontAwesomeIcon from '@fortawesome/vue-fontawesome';
// // the library
// import FontAwesome from '@fortawesome/fontawesome';
// // add more icon categories as you want them, even works with pro packs
// import solids from '@fortawesome/fontawesome-free-solid';
// import brands from '@fortawesome/fontawesome-free-brands';
// import {library} from '@fortawesome/fontawesome-svg-core';
// import {faBackspace, faCalendarDay, faCarSide, faTrashRestore} from '@fortawesome/free-solid-svg-icons';
import { computed } from 'vue';
import AppFlag from '@/components/core/AppFlag.vue';

const props = defineProps({
    icon: {
        type: String,
        default: null
    },
    size: {
        type: String,
        default: undefined,
        validator: value => ['x-small', 'small', 'large', 'x-large'].includes(value)
    },
    color: {
        type: String,
        default: null
    },
    left: {
        type: Boolean,
        default: null
    },
});
const isIcon = computed(() => props.icon?.startsWith('$') || props.icon?.startsWith('mdi-'))
/*
associate it to the library, if you need to add more you can separate them by a comma
FontAwesome.library.add(solids, brands);
library.add(faTrashRestore);
library.add(faCarSide);
library.add(faBackspace);
library.add(faCalendarDay);
*/
</script>
