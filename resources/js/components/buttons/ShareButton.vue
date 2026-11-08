<template>
  <AppButton
    :title="title"
    :loading="loading"
    :modal="modal"
    icon="mdi-share-variant"
    density="compact"
    size="small"
    variant="text"
    @click="showModal = true"
  >
    <template #modal>
      <AppAlert
        :message="Translator.t('help.share_talent_with_the_world')"
        variant="tonal"
        border="start"
        class="mb-3"
      />

      <AppButton
        v-clipboard="url"
        v-flash-message="Translator.actionTitle('copied', null, false)"
        variant="text"
        icon="mdi-clipboard"
        class="float-right"
      />

      <div v-if="model" class="d-flex flex-column p-3 mx-auto">
        <app-field
          :model-value="model.nick_name || model.name"
          :label="Translator.ta('name')"
          inline
        />
        <app-field
          :model-value="url"
          inline
          label="URL"
          class="cursor-select"
        />
      </div>

      <div class="my-5">
        <share-network
          v-for="network in networks"
          :key="network.network"
          :network="network.network"
          :style="{backgroundColor: network.color}"
          :url="sharing.url"
          :title="sharing.title"
          :description="sharing.description"
          :quote="sharing.quote"
          :hashtags="sharing.hashtags"
          :twitter-user="sharing.twitterUser"
          class="p-1 pb-2 rounded-lg mx-1"
        >
          <app-icon
            :title="network.name"
            :icon="network.icon"
            color="white"
          />
        </share-network>
      </div>
    </template>
  </AppButton>
</template>
<script setup>
import { computed, ref, toRef } from 'vue';
import Translator from '@/objects/Translator';
import { useRouter } from 'vue-router';
import useHttp from '@/composables/useHttp';
import Utils from '@/objects/Utils';
import AppButton from '@/components/core/AppButton.vue';
import AppAlert from '@/components/core/AppAlert.vue';
import AppIcon from '@/components/core/AppIcon.vue';
import AppField from '@/components/core/AppField.vue';
import { default as vClipboard } from '@/directives/clipboard';
import { default as vFlashMessage } from '@/directives/flashMessage';

const props = defineProps({
    model: {
        type: Object,
        default: null,
        required: true,
    },
});
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    title: title.value,
    loading: loading.value,
    hideActions: true
  },
  listeners: {
    close: () => showModal.value = false
  }
}));
const showModal = ref(false);
const model = toRef(props, 'model');
const { loading } = useHttp();
const networks = [
    { network: 'facebook', icon: '$facebook', name: 'Facebook', color: '#1877f2' },
    { network: 'linkedin', icon: '$linkedin', name: 'LinkedIn', color: '#007bb5' },
    { network: 'twitter', icon: '$twitter', name: 'Twitter', color: '#1da1f2' },
    // { network: 'whatsapp', name: 'Whatsapp', icon: 'fab fah fa-lg fa-whatsapp', color: '#25d366' },
];
const title = computed(() => Translator.actionTitle('share'));
// const isShareable = computed(() => ['track', 'design', 'user', 'album'].includes(model.value?.model));
const routeData = computed(() => {
    let name;

    if (model.value?.model === 'user') {
        name = 'users.show';
    } else if (model.value?.model === 'album') {
        name = 'albums.show';
    } else if (model.value?.model === 'battle') {
        name = 'battles.show';
    } else {
        name = getCreationRouteName();
    }
    return useRouter().resolve({
        name: name,
        params: { id: model.value?.id }
    }) || {};
});
const tags = computed(() => {
    const tags = model.value?.tags || [];

    return tags.map(tag => tag.name);
});
const url = computed(() => Utils.getFullUrl(routeData.value?.href));
const battleName = computed(() => (model.value?.tracks || model.value?.designs || [])
    .map(creation => creation?.name)
    .filter(Boolean)
    .join(' vs '));
const sharing = computed(() => ({
    url: url.value,
    title: model.value?.nick_name || model.value?.name || battleName.value,
    description: model.value?.description || model.value?.bio,
    quote: model.value?.quote,
    hashtags: tags.value.join(','),
    // twitterUser: '',
}));

const getCreationRouteName = () => {
    if (model.value?.model === 'design') {
        return 'designs.show'
    }

    switch (model.value?.track_type) {
        case 'beat':
            return 'beats.show';
        case 'sample':
            return 'samples.show';
        case 'song':
            return 'songs.show';
    }
}
</script>
