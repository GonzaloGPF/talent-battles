<template>
  <app-button
    :loading="loading"
    :disabled="! isLogged"
    :title="title"
    :modal="modal"
    color="blue darken-4"
    icon="mdi-cloud-upload"
    elevation="6"
    @click="showModal = true"
  >
    <template #modal>
      <AccountAlerts
        v-if="! user.is_account_ready"
        class="mb-2 max-w-lg"
        @navigate="showModal = false"
      />
      <DocsButton
        v-if="showModal"
        path="upload"
        class="float-right"
      />
      <v-tabs
        v-model="tab"
        :items="tabs"
        fixed-tabs
        show-arrows
        centered
        class="lg:min-w-min mb-3"
      />
      <v-window v-model="tab">
        <v-window-item :value="TRACK">
          <track-form
            ref="trackFormElement"
            class="mt-5"
          />
        </v-window-item>
        <v-window-item :value="DESIGN">
          <design-form
            ref="designFormElement"
            class="mt-5"
          />
        </v-window-item>
      </v-window>
    </template>
  </app-button>
</template>
<script setup>
import EventBus from '@/events/EventBus';
import EventTypes from '@/events/EventTypes';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import {computed, defineAsyncComponent, ref} from 'vue';
import { useAuth } from '@/stores/auth';
import AppButton from '@/components/core/AppButton.vue';
import TrackForm from '@/components/forms/TrackForm.vue';
import DesignForm from '@/components/forms/DesignForm.vue';
import { storeToRefs } from 'pinia';
import { useDisplay } from 'vuetify';

const AccountAlerts = defineAsyncComponent(() => import('@/components/alerts/AccountAlerts.vue'))
const DocsButton = defineAsyncComponent(() => import('@/components/buttons/DocsButton.vue'))

const TRACK = 'track';
const DESIGN = 'design';

const { loading, postJson } = useHttp();
const { isLogged, user } = storeToRefs(useAuth());
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value,
    title: title.value,
  },
  listeners: {
    close: () => showModal.value = false,
    ok: uploadCreation,
  }
}))
const showModal = ref(false);
const designFormElement = ref(null);
const trackFormElement = ref(null);
const tab = ref(TRACK);
const title = computed(() => Translator.actionTitle('upload') + ' ' + Translator.tl('creation'));
const { lg, xl, xxl } = useDisplay();
const isBigScreen = computed(() => (lg.value || xl.value || xxl.value));
const tabs = computed(() => [
  {
    title: Translator.modelTitle(TRACK),
    text: isBigScreen.value ? Translator.modelTitle(TRACK) : null,
    value: TRACK,
    prependIcon: '$track',
  },
  {
    title: Translator.modelTitle(DESIGN),
    text: isBigScreen.value ? Translator.modelTitle(DESIGN) : null,
    value: DESIGN,
    prependIcon: '$design',
  },
]);

const uploadCreation = () => {
    if (tab.value === TRACK) {
        return uploadTrack();
    }

    if (tab.value === DESIGN) {
        return uploadDesign();
    }
}

const uploadTrack = async () => {
    const data = trackFormElement.value.validate(true);

    if (! data) return;

    await postJson('tracks', data);

    trackFormElement.value?.reset();
    showModal.value = false;

    EventBus.emit(EventTypes.new_track);
}

const uploadDesign = async () => {
    const data = designFormElement.value.validate(true);

    if (! data) return;

    await postJson('designs', data);

    designFormElement.value?.reset();
    showModal.value = false;

    EventBus.emit(EventTypes.new_design);
}
</script>
