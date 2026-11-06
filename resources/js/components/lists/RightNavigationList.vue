<template>
  <div v-if="isLogged">
    <v-list
      class="!py-0"
      nav
    >
      <!--<v-list-subheader>Controls</v-list-subheader>-->
      <points-item-list />
      <notification-item-list />
    </v-list>
    <v-divider />
    <v-list
      :selected="selectedValue"
      :items="items"
      color="blue"
      nav
      class="!py-0"
      @update:selected="navigate"
    />
    <admin-navigation-list />
  </div>
</template>
<script setup>
import { computed } from 'vue';
import useNavigationList from '@/composables/useNavigationList';
import Translator from '@/objects/Translator';
import NotificationItemList from '@/components/listsItems/NotificationItemList.vue';
import PointsItemList from '@/components/listsItems/PointsItemList.vue';
import { storeToRefs } from 'pinia';
import { useAuth } from '@/stores/auth';
import AdminNavigationList from '@/components/lists/AdminNavigationList.vue';

const { isLogged } = storeToRefs(useAuth());
const items = computed(() => [
  // {
  //   type: 'subheader',
  //   title: Translator.tl('management'),
  //   props: {
  //     class: 'brand-font'
  //   }
  // },
  {
    value: 'stats.summary',
    title: Translator.tl('stats'),
    props: {
      prependIcon: 'mdi-chart-pie',
    }
  },
  {
    value: 'admin.products.index',
    title: Translator.tl('commerce'),
    props: {
      prependIcon: '$product',
    }
  },
  {
    type: 'divider',
    inset: true
  },
  {
    value: 'admin.battles.index',
    title: Translator.modelTitle('battle', true),
    props: {
      prependIcon: '$battle',
    }
  },
  {
    value: 'admin.tracks.index',
    title: Translator.modelTitle('track', true),
    props: {
      prependIcon: '$track',
    }
  },
  {
    value: 'admin.designs.index',
    title: Translator.modelTitle('design', true),
    props: {
      prependIcon: '$design',
    }
  },
  {
    value: 'admin.albums.index',
    title: Translator.modelTitle('album', true),
    props: {
      prependIcon: '$album',
    }
  },
  {
    value: 'admin.favorites.index',
    title: Translator.modelTitle('favorite', true),
    props: {
      prependIcon: '$favorite',
    }
  },
  {
    value: 'admin.reports.index',
    title: Translator.modelTitle('report', true),
    props: {
      prependIcon: '$report',
    }
  },
  // {
  //   type: 'divider',
  //   inset: true
  // },
  // {
  //   value: 'admin.orders.index',
  //   title: Translator.modelTitle('order', true),
  //   props: {
  //     prependIcon: '$order',
  //   }
  // },
  // {
  //   value: 'admin.sales.index',
  //   title: Translator.modelTitle('sale', true),
  //   props: {
  //     prependIcon: '$sale',
  //   }
  // },
  // {
  //   value: 'admin.emails.index',
  //   title: Translator.modelTitle('email', true),
  //   props: {
  //     prependIcon: '$email',
  //   }
  // },
]);
const { selectedValue, navigate } = useNavigationList(items);
</script>
