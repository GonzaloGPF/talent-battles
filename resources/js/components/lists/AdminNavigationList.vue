<template>
  <v-list
    v-if="isInternal"
    :selected="selectedValue"
    :items="items"
    color="blue"
    nav
    class="!pt-0"
    @update:selected="navigate"
  />
</template>
<script setup>
import { computed } from 'vue';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import useNavigationList from '@/composables/useNavigationList';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';

const { isInternal } = storeToRefs(useAuth());

const items = computed(() => [
  {
    type: 'divider',
    inset: true
  },
  // {
  //   type: 'subheader',
  //   title: Translator.tl('internal'),
  //   props: {
  //     class: 'brand-font'
  //   }
  // },
  {
    value: 'admin.users.index',
    title: Translator.modelTitle('user', true),
    props: {
      prependIcon: '$users',
    }
  },
  {
    value: 'admin.emails.index',
    title: Translator.modelTitle('email', true),
    props: {
      prependIcon: '$email',
    }
  },
  {
    value: () => Utils.openTab('horizon'),
    title: 'Horizon',
    props: {
      prependIcon: 'mdi-monitor',
    }
  },
]);
const { selectedValue, navigate } = useNavigationList(items);
</script>