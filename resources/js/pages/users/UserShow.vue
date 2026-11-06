<template>
  <div>
    <v-row>
      <v-col :cols="12" :md="4">
        <user-card :user="user" />

        <text-sheet
          v-if="user?.nick || user.gender || user.birthdate || user.bio || user?.skills"
          :title="Translator.tl('about_me')"
          class="mt-5"
        >
          <app-field
            v-if="user?.nick"
            :label="Translator.ta('name')"
            :model-value="user?.name"
            inline
          />
          <div class="d-flex align-center space-x-3 mb-2">
            <app-icon
              v-if="user.gender"
              :title="Translator.tConstName('gender', user.gender === 'male' ? 'male' : 'female')"
              :icon="user.gender === 'male' ? 'mdi-gender-male' : 'mdi-gender-female'"
              :color="user.gender === 'male' ? 'blue' : 'pink'"
            />
            <app-field
              v-if="user.birthdate"
              :title="Translator.ta('birthdate')"
              :model-value="user.birthdate"
              format="long"
              class="text-caption"
              type="date"
            />
          </div>

          <app-text
            :model-value="user.bio"
            class="text-body-1"
          />

          <app-field
            v-if="user?.skills"
            :label="Translator.ta('skills')"
            :model-value="user?.skills"
            constant="skill"
            class="my-2"
          />
        </text-sheet>
      </v-col>
      <v-col :cols="12" :md="4">
        <v-card class="d-flex flex-column">
          <v-card-text>
            <user-info :model-value="user" />
          </v-card-text>
          <v-card-actions class="mt-auto">
            <reaction-buttons
              :model="user"
              :actions="UserHelper.actionButtons.filter(button => button !== 'info')"
            />
          </v-card-actions>
        </v-card>
      </v-col>
      <v-col :cols="12" :md="4">
        <last-activities-list :user="user" />
      </v-col>
    </v-row>

    <v-row v-if="user.id">
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
        <comments-manager :model="user" />
      </v-col>
    </v-row>
  </div>
</template>
<script setup>
import UserCard from '@/components/cards/UserCard.vue';
import CommentsManager from '@/components/modules/comments/CommentsManager.vue';
import UserInfo from '@/components/info/UserInfo.vue';
import TracksFetcher from '@/components/lists/TracksFetcher.vue';
import DesignsFetcher from '@/components/lists/DesignsFetcher.vue';
import TextSheet from '@/components/shared/TextSheet.vue';
import LastActivitiesList from '@/components/lists/LastActivitiesList.vue';
import { computed } from 'vue';
import UserHelper from '@/helpers/UserHelper';
import Translator from '@/objects/Translator';
import Utils from '@/objects/Utils';
import { storeToRefs } from 'pinia';
import ReactionButtons from '@/components/buttons/ReactionButtons.vue';
import { useUser } from '@/stores/users';
import AppIcon from '@/components/core/AppIcon.vue';
import AppText from '@/components/core/AppText.vue';
import AppField from '@/components/core/AppField.vue';
import { onBeforeRouteUpdate } from 'vue-router';

const { user } = storeToRefs(useUser());
const { read } = useUser();
const params = computed(() => ({
  limit: 3,
  created_by: [user.value?.id],
  randomize: true,
}))
const beatsParams = computed(() => ({ ...params.value, track_types: ['beat'] }));
const beatsRoute = computed(() => ({
  name: 'beats.index',
  query: Utils.copy(beatsParams.value, ['limit', 'randomize'])
}));
const songsParams = computed(() => ({ ...params.value, track_types: ['song'] }));
const songsRoute = computed(() => ({
  name: 'songs.index',
  query: Utils.copy(songsParams.value, ['limit', 'randomize'])
}));
const samplesParams = computed(() => ({ ...params.value, track_types: ['sample'] }));
const samplesRoute = computed(() => ({
  name: 'samples.index',
  query: Utils.copy(samplesParams.value, ['limit', 'randomize'])
}));
const designsParams = computed(() => ({ ...params.value }));
const designsRoute = computed(() => ({
  name: 'designs.index',
  query: Utils.copy(designsParams.value, ['limit', 'randomize'])
}));

onBeforeRouteUpdate((to) => {
  if (to.params.id !== user.value.id) {
    read(to.params.id);
  }
})
</script>
