<template>
  <div>
    <v-list
      :selected="selectedValue"
      :items="items"
      color="blue"
      class="flex flex-column h-100"
      nav
      @update:selected="navigate"
    />
    <contact-button
      v-show="false"
      ref="contactElement"
    />
  </div>
</template>
<script setup>
import { computed, ref } from 'vue';
import useNavigationList from '@/composables/useNavigationList';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import ContactButton from '@/components/buttons/ContactButton.vue';

const items = computed(() => [
  {
    title: Translator.modelTitle('battle', true),
    value: 'battles.index',
    props: {
      prependIcon: '$battle',
    }
  },
  {
    title: Translator.tl('beats'),
    value: 'beats.index',
    props: {
      prependIcon: '$beat',
    }
  },
  {
    title: Translator.tl('songs'),
    value: 'songs.index',
    props: {
      prependIcon: '$song',
    }
  },
  {
    title: Translator.tl('samples'),
    value: 'samples.index',
    props: {
      prependIcon: '$sample',
    }
  },
  {
    title: Translator.modelTitle('design', true),
    value: 'designs.index',
    props: {
      prependIcon: '$design',
    }
  },
  {
    title: Translator.modelTitle('user', true),
    value: 'users.index',
    props: {
      prependIcon: '$users',
    }
  },
  {
    type: 'divider',
    inset: true,
    props: {
      class: 'mt-auto'
    }
  },
  {
    title: Translator.tl('contributors'),
    value: 'contributors.index',
    props: {
      prependIcon: '$donation',
    }
  },
  {
    title: Translator.tl('contact'),
    value: () => contactElement.value?.openModal(),
    props: {
      prependIcon: '$email',
    }
  },
  {
    title: Translator.tl('docs'),
    value: () => Utils.openTab(`docs/${ Translator.getLocale() }`),
    props: {
      prependIcon: '$help',
    }
  },
]);
const { selectedValue, navigate } = useNavigationList(items);
const contactElement = ref();
</script>
