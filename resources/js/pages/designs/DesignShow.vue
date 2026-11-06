<template>
  <v-container>
    <v-row class="mt-8">
      <v-col :cols="12" lg="4">
        <design-card :design="design" />
      </v-col>
      <v-col :cols="12" lg="4">
        <v-card class="d-flex flex-column">
          <v-card-text>
            <design-info :model-value="design" />
          </v-card-text>
          <v-card-actions class="mt-auto">
            <reaction-buttons
              :model="design"
              :actions="DesignHelper.actionButtons.filter(button => button !== 'info')"
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
        <designs-fetcher
          v-if="user.id"
          :title="listTitle"
          :params="params"
          :route="listRoute"
        />
      </v-col>
      <v-col :cols="12" lg="4">
        <comments-manager :model="design" />
      </v-col>
    </v-row>
  </v-container>
</template>
<script setup>
import DesignInfo from '@/components/info/DesignInfo.vue';
import UserCard from '@/components/cards/UserCard.vue';
import CommentsManager from '@/components/modules/comments/CommentsManager.vue';
import DesignCard from '@/components/cards/DesignCard.vue';
import DesignsFetcher from '@/components/lists/DesignsFetcher.vue';
import { useDesign } from '@/stores/designs'
import { computed, ref } from 'vue';
import { useRoute } from 'vue-router';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import DesignHelper from '@/helpers/DesignHelper';
import ReactionButtons from '@/components/buttons/ReactionButtons.vue';
import { storeToRefs } from 'pinia';
import UserHelper from '@/helpers/UserHelper';

const { getJson } = useHttp();
const user = ref({});
const route = useRoute();
const { design } = storeToRefs(useDesign());
const params = computed(() => ({
  created_by: [user.value.id],
  // design_type: [this.design.value?.design_type],
  randomize: true,
  limit: 3,
  except_ids: [route.params.id]
}))
const listRoute = computed(() => ({
  name: 'designs.index',
  query: { created_by: [user.value.id] }
}))
const listTitle = computed(() =>
  Translator.t('help.more_data_from', {
    data: Translator.modelTitle('design', true),
    user: user.value?.nick_name
  }));

getUser();

function getUser() {
  getJson(`users/${ design.value?.created_by }`, { params: UserHelper.showParams })
    .then(onSuccess)
}

function onSuccess({ data }) {
  user.value = data
}
</script>
