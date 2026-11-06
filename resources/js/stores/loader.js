import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useLoader = defineStore('loader', () => {
    const loading = ref(false);
    const setLoading = (value) => loading.value = !! value;

    return {
        loading,
        setLoading,
    }
})
