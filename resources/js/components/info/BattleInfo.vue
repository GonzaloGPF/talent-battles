<template>
  <v-sheet
    color="grey-darken-3"
    rounded="xl"
    border="border"
    elevation="6"
    class="p-5 max-w-5xl mx-auto"
    style="min-height: 490px"
  >
    <div class="d-flex flex-column flex-md-row justify-center align-center items-center w-full">
      <battle-subject
        :subject="subjectA"
        :points="subjectAPoints"
        :is-winner="isWinner(subjectA)"
        :is-finished="true"
        class="w-60 md:w-96 animate__animated animate__fadeIn"
      />

      <div>
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

      <battle-subject
        :subject="subjectB"
        :points="subjectBPoints"
        :is-winner="isWinner(subjectB)"
        :is-finished="true"
        class="w-60 md:w-96 animate__animated animate__fadeIn"
      />
    </div>
    <span
      class="d-block text-center mt-4 subtitle-2 font-weight-medium"
      :class="{'text-green': winner, 'text-blue': !winner}"
      v-text="resultText"
    />
    <div class="text-center mt-2">
      <v-btn
        :to="{ name: 'battles.show', params: { id: battle.id } }"
        :text="Translator.tl('see_battle')"
        append-icon="mdi-arrow-right"
        variant="text"
        size="small"
      />
    </div>
  </v-sheet>
</template>
<script setup>
import BattleSubject from '@/components/cards/BattleSubject.vue';
import {computed} from 'vue';
import Translator from '@/objects/Translator';

const props = defineProps({
    battle: {
        type: Object,
        required: true,
        default: null
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const resultText = computed(() => {
    if (props.battle?.winner) {
        return Translator.tl('won_the_battle', { name: props.battle?.winner.name });
    }

    return Translator.tl('is_draw');
})

const winner = computed(() => props.battle?.winner);
const subjects = computed(() => props.battle?.tracks || props.battle?.designs || [null, null])
const subjectA = computed(() => subjects.value[0]);
const subjectB = computed(() => subjects.value[1]);
const subjectAPoints = computed(() => sumVotes(props.battle, subjectA.value));
const subjectBPoints = computed(() => sumVotes(props.battle, subjectB.value));

const isWinner = (subject) => {
    if (! subject || ! winner.value) return false;

    return winner.value.id === subject.id;
}

function sumVotes(battle, subject) {
    if (! subject || ! battle) return 0;

    return (battle.votes || [])
        .filter(vote => vote.votable_id === subject.id)
        .map(vote => vote.value)
        .reduce((a, b) => a + b, 0)
}
</script>
