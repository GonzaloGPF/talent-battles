<template>
  <app-button
    v-if="isLogged"
    :title="Translator.tl('profile')"
    :size="xs ? 'small' : undefined"
    icon="$profile"
  >
    <template #menu>
      <v-menu
        activator="parent"
        offset-y
      >
        <v-list
          :selected="selectedValue"
          :items="items"
          @update:selected="navigate"
        />
      </v-menu>
    </template>
  </app-button>
</template>
<script setup>
import { useAuth } from '@/stores/auth';
import Translator from '@/objects/Translator';
import AppButton from '@/components/core/AppButton.vue';
import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import useNavigationList from '@/composables/useNavigationList';
import Formatter from '@/objects/Formatter';
import { useDisplay } from 'vuetify';

const { logout } = useAuth();
const { user, isLogged } = storeToRefs(useAuth());
const { xs } = useDisplay();

const items = computed(() => [
  {
    type: 'subheader',
    title: Formatter.text(user.value?.name, 15),
    props: {
      class: 'brand-font'
    }
  },
  {
    value: 'profile.show',
    title: Translator.tl('profile'),
    props: {
      prependIcon: '$profile',
      routeParams: { id: user.value?.id }
    }
  },
  {
    value: () => logout(),
    title: Translator.actionTitle('exit'),
    props: {
      prependIcon: 'mdi-exit-to-app',
    }
  },
])
const { selectedValue, navigate } = useNavigationList(items);
</script>
