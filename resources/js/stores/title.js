import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useTitle = defineStore('title', () => {
    const title = ref();

    const setTitle = (value) => {
        title.value = value;
        document.title = value;
    };

    return {
        title,
        setTitle,
    }
});
