import Vuelidate from '@/objects/Vuelidate';
import useVuelidate from '@vuelidate/core';

const fileInputs = ['files', 'file', 'audio', 'audios', 'image', 'images'];

export default class FormValidator {
    constructor(rules = {}, inputNames = null) {
        this.rules = Vuelidate.processRules(rules);
        this.inputNames = inputNames;
        this.validator = null;
    }

    static fileInputs() {
        return fileInputs;
    }

    validate(data) {
        this.validator = useVuelidate(this.rules, data);

        this.validator.value.$validate();

        this.errors = this.getErrors();

        if (this.validator.value.$invalid) {
            console.error('Validation Errors:', this.validator.value.$errors.map(error => error.$message).join(' '));
            return null;
        } else {
            this.errors = null;
        }

        return true;
    }

    isValid() {
        this.validator.value.$touch();

        return ! this.validator.value.$invalid;
    }

    hasRequired(data, inputName) {
        return !! this.validator[inputName].required;
    }

    clearValidation() {
        // if (! this.validator.value) return;
        // this.validator.value?.resetValidation(); // TODO: not working
    }

    getErrors() {
        const errors = {};

        this.validator.value.$errors
            .forEach(error => {
                if (!errors[error.$property]) {
                    errors[error.$property] = [];
                }

                errors[error.$property].push(error.$message);
            });

        return errors;
    }
}
