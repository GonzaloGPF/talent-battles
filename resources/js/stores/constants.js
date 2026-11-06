import global from '@/config/global';
import { defineStore } from 'pinia';
import { computed, ref, watch } from 'vue';
import useHttp from '@/composables/useHttp';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

const LOCAL_CONSTANTS_NAME = global.getAppName() + '_constants';

let savedConstants = JSON.parse(localStorage.getItem(LOCAL_CONSTANTS_NAME) || null);
let lastLocale = null;

export const useConstant = defineStore('constants', () => {
    const { getJson } = useHttp();
    const locale = computed(() => Translator.getLocale());

    const constants = ref(savedConstants || {});
    const loaded = ref(false);
    /**
     * Returns all values of the given Constant
     *
     * @param {string} constantName
     * @returns {function(*=): *}
     */
    const getConstants = (constantName) => {
        constantName = Formatter.plural(Formatter.snakeCase(constantName)); // A bit of normalization

        return constants.value[constantName]
            .map(name => ({
                id: name,
                label: Translator.tConstName(constantName, name)
            }));
    }
    const read = async () => {
        if (lastLocale === locale.value) return;

        lastLocale = locale.value;

        const { data } = await getJson('constants');

        return constants.value = data;
    }

    watch(locale, () => read());

    return {
        loaded,
        getConstants,
        read,
    }
})
