import { computed, reactive, ref, watch } from 'vue';
import FormValidator from '@/objects/FormValidator';
import Utils from '@/objects/Utils';

export default (modelValue, rules = {}, inputNames = []) => {
    // const formData = ref(modelValue);
    inputNames.push('id', 'created_by');
    const errorsData = ref({});
    const form = reactive(Object.fromEntries(inputNames.map(inputName => [inputName, null])));

    if (modelValue) {
        setForm(modelValue)
    }

    const fvInstance = new FormValidator(rules, inputNames);

    const isCreating = computed(() => ! form?.id);

    function validate(asFormData) {
        const valid = fvInstance.validate(form);

        if (! valid) {
            errorsData.value = fvInstance.getErrors();
            return null
        }

        return processData(form, asFormData);
    }

    function setForm(data) {
        Object.keys(form)
            .forEach(input => form[input] = data[input]);
    }

    function isValid() {
        return fvInstance.isValid(form);
    }

    function hasRequired(inputName) {
        return fvInstance.hasRequired(inputName);
    }

    function clearValidation() {
        errorsData.value = null;
        fvInstance.clearValidation();
    }

    function errors(inputName) {
        return errorsData.value?.[inputName];
    }

    function reset() {
        setForm({})
    }

    // watch(formData, () => {
    //     clearValidation();
    //     setForm(formData.value);
    // });

    watch(form, clearValidation);

    return {
        // validator,
        form,
        setForm,
        validate,
        isValid,
        clearValidation,
        reset,
        errors,
        hasRequired,
        isCreating,
    }
}
function processData(form, asFormData) {
    return shouldFormData(form, asFormData)
        ? Utils.toFormData(form)
        : form;
}
function shouldFormData(data, asFormData) {
    if (asFormData) return true;

    return Object.keys(data)
        .some(input => FormValidator.fileInputs().includes(input))
}
