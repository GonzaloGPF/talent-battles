<template>
  <v-sheet
    color="grey-darken-3"
    rounded="xl"
    border="border"
    elevation="6"
    class="p-5 max-w-5xl mx-auto"
    style="min-height: 600px"
  >
    <h3
      v-if="title"
      class="text-h5 font-weight-bold mb-5 text-center animate__animated animate__rubberBand"
      v-text="title"
    />
    <div class="d-flex flex-col flex-md-row justify-center align-center items-center w-full">
      <BattleSubject
        v-if="subjectA"
        :subject="subjectA"
        :points="BattleHelper.sumVotes(vBattle, subjectA)"
        :timer="timerA"
        :is-winner="BattleHelper.isWinner(vBattle, subjectA)"
        :is-finished="isFinished"
        :loading="loading"
        :disabled="disabled"
        :ready="readyA"
        :show-vote="showVote"
        class="w-100 sm:w-sm animate__animated animate__fadeIn"
        @vote="$emit('vote', subjectA)"
      />

      <div class="my-3">
        <v-sheet
          class="d-flex justify-center align-center my-3 mx-5 brand-font text-center text-4xl w-18 h-18 !border-white !border-1 select-none shine-me"
          rounded="circle"
          elevation="18"
          border="md"
          width="70"
          height="70"
        >
          VS
        </v-sheet>
      </div>

      <BattleSubject
        v-if="subjectB"
        :subject="subjectB"
        :points="BattleHelper.sumVotes(vBattle, subjectB)"
        :timer="timerB"
        :is-winner="BattleHelper.isWinner(vBattle, subjectB)"
        :is-finished="isFinished"
        :loading="loading"
        :disabled="disabled"
        :ready="readyB"
        :show-vote="showVote"
        class="w-100 sm:w-sm animate__animated animate__fadeIn"
        @vote="$emit('vote', subjectB)"
      />
    </div>
    <div
      v-if="showHelp"
      class="text-right"
    >
      <docs-button path="battles" />
    </div>
    <span
      v-if="bottomText"
      class="d-block text-center mt-4 subtitle-2 font-weight-medium"
      :class="{'text-green': !isDraw, 'text-blue': isDraw}"
      v-text="bottomText"
    />
    <AppAlert
      v-if="!isLogged"
      :message="Translator.t('help.must_be_logged_to_vote')"
      class="max-w-sm mx-auto"
      variant="tonal"
      density="compact"
    />
  </v-sheet>
</template>
<script setup>
import {computed, defineAsyncComponent, toRef} from 'vue';
import BattleHelper from '@/helpers/BattleHelper';
import Translator from '@/objects/Translator';
import {useAuth} from '@/stores/auth';
import {storeToRefs} from 'pinia';

defineEmits(['vote']);
const props = defineProps({
    battle: {
        type: Object,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    },
    disabled: {
        type: Boolean,
        default: false
    },
    showHelp: {
        type: Boolean,
        default: false
    },
    showVote: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: null
    },
    bottomText: {
        type: String,
        default: null
    },
    readyA: {
        type: Boolean,
        default: false
    },
    readyB: {
        type: Boolean,
        default: false
    },
    timerA: {
        type: Number,
        default: null
    },
    timerB: {
        type: Number,
        default: null
    },
});
const DocsButton = defineAsyncComponent(() => import('@/components/buttons/DocsButton.vue'));
const AppAlert = defineAsyncComponent(() => import('@/components/core/AppAlert.vue'));
const BattleSubject = defineAsyncComponent(() => import('@/components/cards/BattleSubject.vue'));
const { isLogged } = storeToRefs(useAuth());
const vBattle = toRef(props, 'battle')
const isFinished = computed(() => vBattle.value?.battle_state === 'finished');
const subjects = computed(() => vBattle.value?.tracks || vBattle.value?.designs || [null, null]);
const isDraw = computed(() => isFinished.value && ! vBattle.value?.winner);
const subjectA = computed(() => subjects.value[0]);
const subjectB = computed(() => subjects.value[1]);
</script>
