import global from '@/config/global';
import axios from '@/vendor/axios';
import { defineStore } from 'pinia';
import { ref } from 'vue';

const NAME = global.getAppName() + '_version';

export const useVersion = defineStore('version', () => {
    const version = ref(localStorage.getItem(NAME));

    const setVersion = (version) => {
        version.value = version;
        localStorage.setItem(NAME, version);
    }

    const fetch = async () => {
        try {
            const { data } = await axios.get('vue-version');
            setVersion(data.hash);
            return data.hash;
        } catch (e) {
            console.error(e);
        }
    }

    return {
        version,
        setVersion,
        fetch,
    }
});
