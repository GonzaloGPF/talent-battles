<template>
  <v-container class="py-8">
    <battle-vote-card
      v-if="canVote"
      :battle="battle"
      :loading="loading"
      @vote="onVote"
    />
    <battle-card
      v-else
      :battle="battle"
      :title="title"
      :bottom-text="bottomText"
      show-help
    />
    <div class="d-flex flex-wrap justify-center align-center ga-3 mt-6">
      <share-button :model="battle" />
      <app-button
        :route="{ name: 'battles.index' }"
        :label="Translator.tl('more_battles')"
        prepend-icon="mdi-sword-cross"
        variant="tonal"
      />
    </div>
    <v-row class="mt-6">
      <v-col
        :cols="12"
        :lg="8"
        :offset-lg="2"
      >
        <comments-manager :model="battle" />
      </v-col>
    </v-row>
  </v-container>
</template>
<script setup>
import AppButton from '@/components/core/AppButton.vue';
import BattleCard from '@/components/cards/BattleCard.vue';
import BattleVoteCard from '@/components/cards/BattleVoteCard.vue';
import ShareButton from '@/components/buttons/ShareButton.vue';
import CommentsManager from '@/components/modules/comments/CommentsManager.vue';
import Formatter from '@/objects/Formatter';
import Translator from '@/objects/Translator';
import useHttp from '@/composables/useHttp';
import { useAuth } from '@/stores/auth';
import { useBattle } from '@/stores/battles';
import { storeToRefs } from 'pinia';
import { computed } from 'vue';

const { postJson, loading } = useHttp();
const battleStore = useBattle();
const { battle } = storeToRefs(battleStore);
const { user } = storeToRefs(useAuth());

const state = computed(() => battle.value?.battle_state);
const creations = computed(() => battle.value?.tracks || battle.value?.designs || []);
const isMine = (model) => !! user.value?.id && model?.created_by === user.value.id;
const isParticipant = computed(() => creations.value.some(isMine));
const hasVoted = computed(() => (battle.value?.votes || []).some(isMine));
const canVote = computed(() => state.value === 'in_progress' && ! isParticipant.value && ! hasVoted.value);
const ending = computed(() => Formatter.date(battle.value?.ending));
const title = computed(() => Translator.tl(`battle_${ state.value }`));
const bottomText = computed(() => {
  if (state.value === 'finished') {
    return battle.value?.winner
      ? Translator.tl('won_the_battle', { name: battle.value.winner.name })
      : Translator.tl('is_draw');
  }

  if (state.value === 'open') {
    return Translator.t('help.waiting_opponent');
  }

  if (hasVoted.value) {
    return Translator.tl('already_voted_until', { date: ending.value });
  }

  return Translator.tl('your_battle_until', { date: ending.value });
});

const onVote = (creation) => {
  postJson(`battles/${ battle.value.id }/votes`, {
    model_id: creation.id,
    model_type: battle.value.battle_type,
  }).finally(() => battleStore.read(battle.value.id));
}
</script>
