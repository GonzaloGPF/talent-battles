<template>
  <app-button
    :title="title"
    :loading="loading"
    :disabled="! isLogged"
    :modal="modal"
    icon="$battle"
    color="orange darken-2"
    elevation="6"
    @click="showModal = true"
  >
    <template #modal>
      <docs-button
        path="battles"
        class="float-right"
      />
      <div class="d-flex flex-column">
        <battle-form
          ref="formElement"
          class="mt-5"
          @update:selected="selected = $event"
        />
        <points-spend-summary
          :title="Translator.actionTitle('create', 'battle')"
          :price="createBattlePoints"
          class="mt-3 mx-auto"
        />
      </div>
    </template>
  </app-button>
</template>
<script setup>
import BattleForm from '@/components/forms/BattleForm.vue';
import PointsSpendSummary from '@/components/shared/PointsSpendSummary.vue';
import useHttp from '@/composables/useHttp';
import { computed, ref } from 'vue';
import Translator from '@/objects/Translator';
import { useAuth } from '@/stores/auth';
import { useCompany } from '@/stores/company';
import AppButton from '@/components/core/AppButton.vue';
import { storeToRefs } from 'pinia';
import DocsButton from '@/components/buttons/DocsButton.vue';

const { postJson, loading } = useHttp();
const { isLogged, user } = storeToRefs(useAuth());
const { createBattlePoints } = storeToRefs(useCompany());
const showModal = ref(false);
const selected = ref(null);
const formElement = ref(null);
const modal = computed(() => ({
  data: {
    modelValue: showModal.value,
    loading: loading.value,
    title: title.value,
    disabled: !canAfford.value
  },
  listeners: {
    close: () => showModal.value = false,
    ok: createBattle,
  }
}));
const title = computed(() => Translator.actionTitle('battle'));
const canAfford = computed(() => {
    const points = user.value?.points || 0;

    return points - createBattlePoints.value > 0;
});
const createBattle = () => {
    const data = formElement.value?.validate();

    if (! data) return;

    return postJson('battles', {
        id: selected.value?.id,
        type: selected.value?.model
    }).then(() => showModal.value = false);
}
</script>
