<template>
  <v-container :fluid="mobile">
    <v-progress-linear
      :active="loading"
      indeterminate
    />
    <battle-vote-card
      v-if="battle"
      :battle="battle"
      :loading="loading"
      @vote="onVote"
    />
    <app-empty
      v-else
      model="battle"
      class="text-center"
    />
  </v-container>
</template>
<script setup>
import BattleVoteCard from '@/components/cards/BattleVoteCard.vue';
import useHttp from '@/composables/useHttp';
import AppEmpty from '@/components/core/AppEmpty.vue';
import BattleHelper from '@/helpers/BattleHelper';
import { ref } from 'vue';
import { useDisplay } from 'vuetify';

const { loading, getJson, postJson } = useHttp();
const { mobile } = useDisplay();
const battle = ref(null);

const params = {
  ...BattleHelper.indexParams(),
  ...{ is_next: true }
};

const getNextBattle = () => {
  getJson('battles', { params })
    .then(onSuccess);
}

const onSuccess = ({ data }) => {
  if (! data.length) {
    battle.value = null;
  } else {
    battle.value = data[0];
  }
}

const onVote = (creation) => {
  if (! battle.value) return;

  postJson(`battles/${ battle.value.id }/votes`, {
    model_id: creation.id,
    model_type: battle.value.battle_type,
  })
    .then(getNextBattle)
    .catch(getNextBattle);
}

getNextBattle();
</script>
