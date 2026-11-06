import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import useHttp from '@/composables/useHttp';

export const useCompany = defineStore('company', () => {
    const { getJson } = useHttp();

    const companyData = ref(null);
    const isLoaded = computed(() => Object.keys(companyData.value || {}).length > 0);
    const battleTTL = computed(() => companyData.value?.battle_ttl);
    const pointPrice = computed(() => companyData?.value?.buy?.points.price);
    const discounts = computed(() => companyData?.value?.buy?.points.discounts);
    const pointsAwardVote = computed(() => companyData?.value?.points.awards.do_vote);
    const promoData = computed(() => companyData?.value?.services.promo);
    const createBattlePoints = computed(() => companyData?.value?.points?.prices?.create_battle);
    const fetchData = async () => {
        let { data } = await getJson('company');

        return companyData.value = data;
    }

    const calculateDiscount = (points) => {
        points = points || 0;

        const discountsValue = discounts.value || {};

        const key = Object.keys(discountsValue)
            .map((minPoints) => parseFloat(minPoints))
            .sort((a, b) => a - b)
            .filter((minPoints) => points <= minPoints)
            .shift();

        return discountsValue[key] || 0;
    }

    return {
        pointPrice,
        discounts,
        isLoaded,
        battleTTL,
        createBattlePoints,
        pointsAwardVote,
        promoData,
        fetchData,
        calculateDiscount,
    }
});
