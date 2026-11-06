<template>
  <v-app>
    <top-menu
      :left-menu="leftDrawer"
      :right-menu="rightDrawer"
      @left-menu-click.stop="leftDrawer = !leftDrawer"
      @right-menu-click.stop="rightDrawer = !rightDrawer"
    />
    <v-navigation-drawer
      :model-value="smAndDown ? leftDrawer : true"
      :rail="! leftDrawer"
      :temporary="smAndDown"
      :permanent="! smAndDown"
      disable-resize-watcher
      @update:model-value="leftDrawer = $event"
    >
      <left-navigation-list class="h-100" />
    </v-navigation-drawer>
    <v-navigation-drawer
      v-if="isLogged"
      :model-value="smAndDown ? rightDrawer : true"
      :rail="! rightDrawer"
      :temporary="smAndDown"
      :permanent="! smAndDown"
      location="right"
      disable-resize-watcher
      @update:model-value="rightDrawer = $event"
    >
      <RightNavigationList v-if="isLogged" />
    </v-navigation-drawer>

    <v-main class="pb-10">
      <AppFeedback />

      <v-container
        fluid
        class="pb-10"
      >
        <AppRouterView />
      </v-container>
    </v-main>

    <app-footer app fixed />
  </v-app>
</template>
<script setup>
import TopMenu from '@/components/menu/TopMenu.vue';
import Echo from '@/objects/Echo';
import EventBus from '@/events/EventBus';
import EventTypes from '@/events/EventTypes';
import {defineAsyncComponent, onMounted, onUnmounted, ref, watch} from 'vue';
import {useAuth} from '@/stores/auth';
import {useErrors} from '@/stores/errors';
import {useFlashMessages} from '@/stores/flashMessages';
import {storeToRefs} from 'pinia';
import AppFooter from '@/components/core/AppFooter.vue';
import Translator from '@/objects/Translator';
import {useSnackbar} from '@/stores/snackbar';
import {useDisplay} from 'vuetify';
import LeftNavigationList from '@/components/lists/LeftNavigationList.vue';
import AppRouterView from '@/components/core/AppRouterView.vue';
import AppFeedback from '@/components/core/AppFeedback.vue'

const RightNavigationList = defineAsyncComponent(() => import('@/components/lists/RightNavigationList.vue'))

const { fetchMe, loadIdentity, setSocketId } = useAuth();
const { user, isLogged } = storeToRefs(useAuth());
const { pushFlashMessage } = useFlashMessages();
const { setErrors } = useErrors();
const flashMessage = ref(window.flash_message);
const leftDrawer = ref(false);
const rightDrawer = ref(false);
const { smAndDown } = useDisplay();

Echo.connect()
  ?.then(setSocketId);

loadIdentity();

watch(user, () => {
  const userId = user.value?.id;

  if (userId) {
    Echo.connectToPrivate(userId);
  } else {
    Echo.leavePrivate();
  }

}, { immediate: true });

onMounted(() => {
  pushFlashMessage(flashMessage.value);
});

onUnmounted(() => Echo.disconnect());

EventBus.on(EventTypes.OrderSuccess, fetchMe);
EventBus.on(EventTypes.errors, setErrors);
EventBus.on(EventTypes.flash_message, pushFlashMessage);
EventBus.on(EventTypes.backend['.UserUpdated'], fetchMe);
EventBus.on(EventTypes.backend.NewDeploy, onNewDeploy);

function onNewDeploy() {
  useSnackbar().showSnackbar(Translator.t('help.update_to_new_version'), {
    timeout: -1,
    title: Translator.t('help.new_version_of_app')
  }, {
    icon: 'mdi-refresh',
    color: 'blue',
    title: Translator.actionTitle('update'),
    onClick: () => window.location.reload(),
  });
}
</script>
