import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useErrors = defineStore('errors', () => {
    const errors = ref({});

    const hasErrors = computed(() => {
        const errorsValue = errors.value || {};

        return Object
            .keys(errorsValue)
            .filter(key => errorsValue[key].length > 0)
            .length > 0;
    });

    const setErrors = (errorsData) => errors.value = errorsData;

    const removeError = (name) => {
        if (! name) {
            errors.value = {}
        }
        if (errors.value.name) {
            errors.value.name = [];
        }
    }

    return {
        errors,
        hasErrors,
        setErrors,
        removeError,
    }
});
