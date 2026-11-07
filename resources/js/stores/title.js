import { defineStore } from 'pinia';
import { ref } from 'vue';

/**
 * Title of the app bar. The title of the browser tab is set by guards/after/documentTitle.
 */
export const useTitle = defineStore('title', () => {
    const title = ref();

    const setTitle = (value) => {
        title.value = value;
    };

    return {
        title,
        setTitle,
    }
});
