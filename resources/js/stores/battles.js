import { defineStore } from 'pinia';
import useHttp from '@/composables/useHttp';
import { ref } from 'vue';
import BattleHelper from '@/helpers/BattleHelper';

export const useBattle = defineStore('battles', () => {
    const { getJson } = useHttp();

    const battle = ref({});
    const read = async (id) => {
        const { data } = await getJson(`battles/${ id }`, { params: BattleHelper.indexParams() });

        return battle.value = data;
    }

    return {
        battle,
        read,
    }
})
