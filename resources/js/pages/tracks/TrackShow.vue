<template>
  <v-container>
    <v-row class="mt-8">
      <v-col :cols="12" lg="4">
        <track-card :track="track" />
      </v-col>
      <v-col :cols="12" lg="4">
        <v-card class="d-flex flex-column">
          <v-card-text>
            <track-info :model-value="track" />
          </v-card-text>
          <v-card-actions class="mt-auto">
            <reaction-buttons
              :model="track"
              :actions="TrackHelper.actionButtons.filter(button => button !== 'info')"
            />
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col :cols="12" lg="4">
        <user-card :user="user" />
      </v-col>
    </v-row>
    <v-row>
      <v-col :cols="12" lg="8">
        <tracks-fetcher
          v-if="user.id"
          :title="listTitle"
          :params="params"
          :route="listRoute"
        />
      </v-col>
      <v-col :cols="12" lg="4">
        <comments-manager :model="track" />
      </v-col>
    </v-row>
  </v-container>
</template>
<script setup>
import TrackCard from '@/components/cards/TrackCard.vue';
import TrackInfo from '@/components/info/TrackInfo.vue';
import UserCard from '@/components/cards/UserCard.vue';
import CommentsManager from '@/components/modules/comments/CommentsManager.vue';
import TracksFetcher from '@/components/lists/TracksFetcher.vue';
import { useTrack } from '@/stores/tracks';
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import useHttp from '@/composables/useHttp';
import UserHelper from '@/helpers/UserHelper';
import Translator from '@/objects/Translator';
import TrackHelper from '@/helpers/TrackHelper';
import ReactionButtons from '@/components/buttons/ReactionButtons.vue';
import { storeToRefs } from 'pinia';
import Formatter from '@/objects/Formatter';

const route = useRoute();
const { getJson } = useHttp();
const { track } = storeToRefs(useTrack());
const user = ref({});
const params = computed(() => ({
  created_by: [user.value?.id],
  track_type: [track.value?.track_type],
  randomize: true,
  limit: 3,
  except_ids: [route.params.id]
}))
const listRoute = computed(() => {
  if (! track.value?.track_type) return {};

  const baseRoute = Formatter.plural(track.value?.track_type);

  return {
    name: `${ baseRoute }.index`,
    query: { created_by: [user.value.id] }
  }
})
const listTitle = computed(() =>
  Translator.t('help.more_data_from', {
    data: Translator.tl(Formatter.plural(track.value?.track_type)),
    user: user.value?.nick_name
  })
);

getUser();

async function getUser() {
  const { data } = await getJson(`users/${ track.value?.created_by }`, { params: UserHelper.indexParams });

  user.value = data;
}
</script>
