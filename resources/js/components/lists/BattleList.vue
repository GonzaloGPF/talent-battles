<template>
  <div>
    <AppProgress
      v-if="loading"
      :loading="loading"
      indeterminate
    />
    <h3
      class="title-brand text-blue text-lg text-center mb-10"
      v-text="Translator.tl('latest_finished_battles')"
    />
    <v-row v-if="battles.length">
      <v-col
        v-for="battle in battles"
        :key="battle.id"
        :cols="12"
        :xl="6"
      >
        <battle-info :battle="battle" />
      </v-col>
    </v-row>
    <app-empty v-else />
  </div>
</template>
<script setup>
import useHttp from '@/composables/useHttp';
import {computed, defineAsyncComponent, ref, watch} from 'vue';
import Translator from '@/objects/Translator';
import AppEmpty from '@/components/core/AppEmpty.vue';
import BattleHelper from '@/helpers/BattleHelper';
import Utils from '@/objects/Utils';
import BattleInfo from '@/components/info/BattleInfo.vue';

const AppProgress = defineAsyncComponent(() => import('@/components/core/AppProgress.vue'));
const props = defineProps({
    params: {
        type: Object,
        default: null
    }
});
const { getJson, loading } = useHttp();
const battles = ref([]);
const vParams = computed(() => ({
    ...BattleHelper.indexParams(),
    is_finished: true,
    ...props.params
}));

const onSuccess = ({ data }) => {
    battles.value = data;
}

const getBattles = () => {
    getJson('battles', { params: vParams.value })
        .then(onSuccess)
}

watch(vParams, (newValue, oldValue) => {
    if (Utils.areEquals(newValue, oldValue)) return;

    getBattles();
}, { immediate: true, deep: true });
</script>
