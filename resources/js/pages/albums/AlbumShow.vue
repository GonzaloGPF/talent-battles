<template>
  <v-container>
    <v-row class="mt-8">
      <v-col :cols="12" lg="4">
        <album-card :album="album" />
      </v-col>
      <v-col :cols="12" lg="4">
        <v-card class="d-flex flex-column fill-height justify-center">
          <v-card-text class="fill-height">
            <album-info :model-value="album" />
          </v-card-text>
          <v-card-actions class="mt-auto">
            <reaction-buttons
              :model="album"
              :actions="AlbumHelper.actionButtons.filter(button => button !== 'info')"
            />
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col :cols="12" lg="4">
        <user-card :user="user" />
      </v-col>
    </v-row>

    <v-row v-if="album.id">
      <v-col :cols="12" lg="8">
        <tracks-fetcher
          :title="Translator.tl('beats')"
          :params="beatsParams"
          :route="beatsRoute"
          class="mt-3"
        />
        <tracks-fetcher
          :title="Translator.tl('songs')"
          :params="songsParams"
          :route="songsRoute"
          class="mt-3"
        />
        <tracks-fetcher
          :title="Translator.tl('samples')"
          :params="samplesParams"
          :route="samplesRoute"
          class="mt-3"
        />
        <designs-fetcher
          :title="Translator.tl('designs')"
          :params="designsParams"
          :route="designsRoute"
          class="mt-3"
        />
      </v-col>
      <v-col :cols="12" lg="4">
        <comments-manager :model="album" />
      </v-col>
    </v-row>
  </v-container>
</template>
<script setup>
import UserCard from '@/components/cards/UserCard.vue';
import AlbumInfo from '@/components/info/AlbumInfo.vue';
import AlbumCard from '@/components/cards/AlbumCard.vue';
import TracksFetcher from '@/components/lists/TracksFetcher.vue';
import DesignsFetcher from '@/components/lists/DesignsFetcher.vue';
import CommentsManager from '@/components/modules/comments/CommentsManager.vue';
import { useAlbum } from '@/stores/albums';
import { computed, ref, watch } from 'vue';
import UserHelper from '@/helpers/UserHelper';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import ReactionButtons from '@/components/buttons/ReactionButtons.vue';
import Utils from '@/objects/Utils';
import AlbumHelper from '@/helpers/AlbumHelper';

let user = ref({});
const { getJson } = useHttp();
const { album } = useAlbum();

const params = computed(() => ({
    limit: 3,
    album: [album.id],
    randomize: true,
}))
const beatsParams = computed(() => Object.assign({}, params.value, { track_types: ['beat'] }));
const beatsRoute = computed(() => ({
        name: 'beats.index',
        query: Utils.copy(beatsParams.value, ['limit', 'randomize'])
    }
))
const songsParams = computed(() => Object.assign({}, params.value, { track_types: ['song'] }));
const songsRoute = computed(() => ({
        name: 'songs.index',
        query: Utils.copy(songsParams.value, ['limit', 'randomize'])
    }
));
const samplesParams = computed(() => Object.assign({}, params.value, { track_types: ['sample'] }));
const samplesRoute = computed(() => ({
        name: 'samples.index',
        query: Utils.copy(samplesParams.value, ['limit', 'randomize'])
    }
));
const designsParams = computed(() => Object.assign({}, params.value));
const designsRoute = computed(() => ({
        name: 'designs.index',
        query: Utils.copy(designsParams.value, ['limit', 'randomize'])
    }
));

async function getUser() {
    const { data } = await getJson(`users/${ album.created_by }`, { params: UserHelper.indexParams });

    user.value = data
}

watch(album, getUser, { immediate: true });
</script>
