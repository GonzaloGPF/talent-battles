import { computed, ref, watch } from 'vue';
import Translator from '@/objects/Translator';
import Formatter from '@/objects/Formatter';

export default (propRefs) => {
    const name = propRefs.name;
    const modelValue = propRefs.modelValue;
    const label = propRefs.label;

    const iValue = ref();

    const iName = computed(() => name.value + '_' + Math.random().toString(36).substring(2));

    const iLabel = computed(() => {
        const labelValue = label?.value;

        if (labelValue === null) return null;

        if (labelValue) {
            return typeof labelValue === 'function'
                ? labelValue()
                : labelValue;
        }

        if (Translator.te(`validation.attributes.${ name.value }`)) {
            return Translator.ta(name.value);
        }

        return Formatter.ucFirst(name.value);
    });

    const setValue = value => iValue.value = value;

    const reset = () => {
        iValue.value = null;
    }

    watch(modelValue, (newValue) => setValue(newValue), { immediate: true, deep: true });

    return {
        // hasRequired,
        // errors,
        iLabel,
        iName,
        iValue,
        setValue,
        reset,
    }
}
